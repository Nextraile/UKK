/**
 * Documentation Sync Watcher Plugin
 * 
 * Monitors core documentation files for changes and detects when
 * syncing is needed between related files.
 * 
 * Watches: SKILL.md, ARCHITECTURE.md, PRD.md, TODO.md, WORKFLOW.md, AGENTS.md, MANUAL.md
 * 
 * When changes detected:
 * 1. Computes file hash to verify actual content change
 * 2. Parses file to extract semantic metadata
 * 3. Compares with previous metadata to detect changes
 * 4. Determines which files need sync
 * 5. Stores pending sync in SQLite
 * 6. Shows toast notification
 */

import { Plugin } from "@opencode-ai/plugin"
import { createHash } from "crypto"
import { readFileSync, existsSync } from "fs"
import { getDatabase, generateSyncId } from "../tools/lib/db-schema.mjs"
import { parseAllDocs, compareMetadata } from "../tools/lib/doc-parser.mjs"
import { determineSyncTargets, generateSyncPlan } from "../tools/lib/sync-planner.mjs"

const WATCHED_FILES = [
  'SKILL.md',
  'ARCHITECTURE.md',
  'PRD.md',
  'TODO.md',
  'WORKFLOW.md',
  'AGENTS.md',
  'MANUAL.md'
]

/**
 * Compute SHA256 hash of file content
 */
function hashFile(filePath) {
  try {
    const content = readFileSync(filePath, 'utf-8')
    return createHash('sha256').update(content).digest('hex')
  } catch (error) {
    return null
  }
}

/**
 * Load current file hashes from database
 */
function loadFileHashes(db, worktree) {
  const hashes = new Map()
  
  const rows = db.prepare('SELECT file, hash, last_metadata FROM file_hashes').all()
  
  for (const row of rows) {
    hashes.set(row.file, {
      hash: row.hash,
      metadata: row.last_metadata ? JSON.parse(row.last_metadata) : null
    })
  }
  
  // Initialize hashes for files that don't exist in DB yet
  for (const file of WATCHED_FILES) {
    const fullPath = `${worktree}/${file}`
    if (existsSync(fullPath) && !hashes.has(file)) {
      const hash = hashFile(fullPath)
      if (hash) {
        db.prepare(`
          INSERT INTO file_hashes (file, hash, last_metadata, updated_at)
          VALUES (?, ?, NULL, ?)
        `).run(file, hash, Date.now())
        
        hashes.set(file, { hash, metadata: null })
      }
    }
  }
  
  return hashes
}

/**
 * Detect and record changes
 */
async function detectChanges(db, client, worktree, file, oldHash, newHash, hashes) {
  const fullPath = `${worktree}/${file}`
  
  // Parse current metadata
  let newMetadata = null
  
  try {
    const allDocs = await parseAllDocs(worktree)
    
    // Map file to metadata key
    const metadataKey = {
      'SKILL.md': 'skill',
      'ARCHITECTURE.md': 'architecture',
      'PRD.md': 'prd',
      'TODO.md': 'todo',
      'WORKFLOW.md': 'workflow',
      'AGENTS.md': 'agents',
      'MANUAL.md': 'manual'
    }[file]
    
    newMetadata = allDocs[metadataKey]
  } catch (error) {
    console.warn(`[doc-sync-watcher] Failed to parse ${file}:`, error.message)
    return
  }
  
  // Get old metadata
  const oldMetadata = hashes.get(file)?.metadata
  
  // Compare metadata to detect semantic changes
  const changes = compareMetadata(oldMetadata, newMetadata)
  
  if (changes.length === 0) {
    console.log(`[doc-sync-watcher] ${file} changed but no semantic changes detected`)
    
    // Update hash only
    db.prepare(`
      UPDATE file_hashes
      SET hash = ?, last_metadata = ?, updated_at = ?
      WHERE file = ?
    `).run(newHash, JSON.stringify(newMetadata), Date.now(), file)
    
    hashes.set(file, { hash: newHash, metadata: newMetadata })
    return
  }
  
  console.log(`[doc-sync-watcher] ${file} semantic changes detected:`, changes)
  
  // Generate sync plan
  const plan = await generateSyncPlan(file, changes, newMetadata)
  
  if (plan.targets.length === 0) {
    console.log(`[doc-sync-watcher] No sync targets for ${file} changes`)
    
    // Update metadata but no sync needed
    db.prepare(`
      UPDATE file_hashes
      SET hash = ?, last_metadata = ?, updated_at = ?
      WHERE file = ?
    `).run(newHash, JSON.stringify(newMetadata), Date.now(), file)
    
    hashes.set(file, { hash: newHash, metadata: newMetadata })
    return
  }
  
  // Store pending sync
  const syncId = generateSyncId()
  
  db.prepare(`
    INSERT INTO pending_syncs (id, trigger_file, trigger_changes, detected_at, status, confidence)
    VALUES (?, ?, ?, ?, ?, ?)
  `).run(
    syncId,
    file,
    JSON.stringify(changes),
    Date.now(),
    'pending',
    plan.targets.reduce((sum, t) => sum + t.confidence, 0) / plan.targets.length
  )
  
  // Store sync targets
  for (const target of plan.targets) {
    for (const edit of target.edits) {
      db.prepare(`
        INSERT INTO sync_targets (sync_id, target_file, target_line, old_content, new_content, confidence)
        VALUES (?, ?, ?, ?, ?, ?)
      `).run(
        syncId,
        target.file,
        edit.line,
        edit.old,
        edit.new,
        target.confidence
      )
    }
  }
  
  // Update file hash and metadata
  db.prepare(`
    UPDATE file_hashes
    SET hash = ?, last_metadata = ?, updated_at = ?
    WHERE file = ?
  `).run(newHash, JSON.stringify(newMetadata), Date.now(), file)
  
  hashes.set(file, { hash: newHash, metadata: newMetadata })
  
  // Show toast notification
  const targetCount = plan.targets.length
  const message = `📄 ${file} changed. ${targetCount} file(s) may need sync.`
  
  try {
    await client.tui.toast.show({
      body: {
        message,
        duration: 5000
      }
    })
    
    console.log(`[doc-sync-watcher] Toast shown: ${message}`)
  } catch (error) {
    console.warn('[doc-sync-watcher] Failed to show toast:', error.message)
  }
}

/**
 * Plugin definition
 */
export const DocSyncWatcher: Plugin = async ({ client, worktree }) => {
  console.log('[doc-sync-watcher] Plugin initializing...')
  
  // Initialize database
  const db = getDatabase(worktree)
  
  // Load current file hashes
  const hashes = loadFileHashes(db, worktree)
  
  console.log(`[doc-sync-watcher] Watching ${WATCHED_FILES.length} files`)
  
  return {
    /**
     * File watcher hook - fires when files are modified
     */
    "file.watcher.updated": async (input, output) => {
      const { path } = input
      
      // Check if this is one of our watched files
      const watchedFile = WATCHED_FILES.find(f => path.endsWith(f))
      if (!watchedFile) return
      
      console.log(`[doc-sync-watcher] File updated: ${watchedFile}`)
      
      // Compute new hash
      const newHash = hashFile(path)
      if (!newHash) {
        console.warn(`[doc-sync-watcher] Failed to hash ${watchedFile}`)
        return
      }
      
      // Get old hash
      const oldData = hashes.get(watchedFile)
      const oldHash = oldData?.hash
      
      // Skip if no actual content change
      if (newHash === oldHash) {
        console.log(`[doc-sync-watcher] ${watchedFile} hash unchanged, skipping`)
        return
      }
      
      console.log(`[doc-sync-watcher] ${watchedFile} content changed (hash mismatch)`)
      
      // Detect and record changes
      await detectChanges(db, client, worktree, watchedFile, oldHash, newHash, hashes)
    },
    
    /**
     * File edited hook - secondary confirmation that edit completed
     */
    "file.edited": async (input, output) => {
      const { path } = input
      
      // Check if this is one of our watched files
      const watchedFile = WATCHED_FILES.find(f => path.endsWith(f))
      if (!watchedFile) return
      
      console.log(`[doc-sync-watcher] File edited (confirmed): ${watchedFile}`)
      
      // The file.watcher.updated hook should have already fired
      // This is just a confirmation that the edit is complete
      // We can use this to double-check or trigger additional logic if needed
    }
  }
}

export default DocSyncWatcher
