/**
 * sync-docs Custom Tool
 * 
 * LLM-callable tool for documentation synchronization
 * 
 * Modes:
 * - check: Generate sync report from pending syncs
 * - preview: Show line-by-line diffs before applying
 * - apply: Execute edits to sync documentation files
 */

import { tool } from "@opencode-ai/plugin"
import { getDatabase } from "./lib/db-schema.mjs"
import { readFileSync, writeFileSync } from "fs"

/**
 * Check mode - generate sync report
 */
function checkMode(db, worktree) {
  const pending = db.prepare(`
    SELECT * FROM pending_syncs 
    WHERE status = 'pending'
    ORDER BY detected_at DESC
  `).all()
  
  if (pending.length === 0) {
    return "✅ No pending syncs. All documentation is consistent."
  }
  
  let report = "## Documentation Sync Report\n\n"
  report += `Pending syncs: ${pending.length}\n\n`
  
  for (const sync of pending) {
    const changes = JSON.parse(sync.trigger_changes)
    const targets = db.prepare(`
      SELECT * FROM sync_targets WHERE sync_id = ?
    `).all(sync.id)
    
    const age = Math.floor((Date.now() - sync.detected_at) / 60000)
    const ageStr = age < 60 ? `${age} minutes ago` : `${Math.floor(age / 60)} hours ago`
    
    report += `### Trigger: ${sync.trigger_file} (changed ${ageStr})\n`
    report += `Sync ID: ${sync.id}\n\n`
    
    report += "Changes detected:\n"
    for (const change of changes) {
      if (change.type === 'version') {
        report += `- Version: ${change.old} → ${change.new}\n`
      } else if (change.type === 'skillCount') {
        report += `- Skill count: ${change.old} → ${change.new}\n`
      } else if (change.type === 'skills') {
        if (change.added && change.added.length > 0) {
          report += `- Skills added: ${change.added.join(', ')}\n`
        }
        if (change.removed && change.removed.length > 0) {
          report += `- Skills removed: ${change.removed.join(', ')}\n`
        }
      } else if (change.type === 'components') {
        if (change.added && change.added.length > 0) {
          report += `- Components added: ${change.added.join(', ')}\n`
        }
      } else if (change.type === 'requirements') {
        if (change.added && change.added.length > 0) {
          report += `- Requirements added: ${change.added.join(', ')}\n`
        }
      }
    }
    report += "\n"
    
    // Group targets by file
    const targetsByFile = {}
    for (const target of targets) {
      if (!targetsByFile[target.target_file]) {
        targetsByFile[target.target_file] = []
      }
      targetsByFile[target.target_file].push(target)
    }
    
    report += "Affected files:\n"
    for (const [file, fileTargets] of Object.entries(targetsByFile)) {
      const avgConfidence = fileTargets.reduce((sum, t) => sum + t.confidence, 0) / fileTargets.length
      const emoji = avgConfidence >= 0.9 ? '✅' : avgConfidence >= 0.7 ? '⚠️' : '❓'
      
      report += `${emoji} **${file}** (confidence: ${(avgConfidence * 100).toFixed(0)}%)\n`
      
      for (const target of fileTargets) {
        if (target.target_line) {
          report += `   - Line ${target.target_line}\n`
        }
      }
    }
    report += "\n"
  }
  
  report += "**Options:**\n"
  report += "1. Preview diffs: `sync-docs({ action: \"preview\" })`\n"
  report += "2. Apply all: `sync-docs({ action: \"apply\" })`\n"
  report += "3. Apply specific: `sync-docs({ action: \"apply\", syncId: \"sync-...\" })`\n"
  
  return report
}

/**
 * Preview mode - show line-by-line diffs
 */
function previewMode(db, worktree, files, syncId) {
  let query = 'SELECT * FROM pending_syncs WHERE status = ?'
  let params = ['pending']
  
  if (syncId) {
    query += ' AND id = ?'
    params.push(syncId)
  }
  
  const pending = db.prepare(query).all(...params)
  
  if (pending.length === 0) {
    return "✅ No pending syncs to preview."
  }
  
  let preview = "## Preview: Documentation Sync Edits\n\n"
  
  for (const sync of pending) {
    preview += `### Sync: ${sync.trigger_file} → multiple files\n`
    preview += `Sync ID: ${sync.id}\n\n`
    
    let targets = db.prepare(`
      SELECT * FROM sync_targets WHERE sync_id = ?
    `).all(sync.id)
    
    // Filter by files if specified
    if (files && files.length > 0) {
      targets = targets.filter(t => files.includes(t.target_file))
    }
    
    // Group by file
    const targetsByFile = {}
    for (const target of targets) {
      if (!targetsByFile[target.target_file]) {
        targetsByFile[target.target_file] = []
      }
      targetsByFile[target.target_file].push(target)
    }
    
    for (const [file, fileTargets] of Object.entries(targetsByFile)) {
      preview += `#### ${file}\n\n`
      
      for (const target of fileTargets) {
        if (target.target_line) {
          preview += `Line ${target.target_line}:\n`
        }
        preview += "```diff\n"
        preview += `- ${target.old_content}\n`
        preview += `+ ${target.new_content}\n`
        preview += "```\n\n"
      }
    }
  }
  
  const totalFiles = new Set()
  const totalEdits = pending.reduce((sum, sync) => {
    const targets = db.prepare('SELECT * FROM sync_targets WHERE sync_id = ?').all(sync.id)
    targets.forEach(t => totalFiles.add(t.target_file))
    return sum + targets.length
  }, 0)
  
  preview += "---\n"
  preview += `**Total:** ${totalFiles.size} file(s), ${totalEdits} edit(s)\n\n`
  preview += "Approve? Call `sync-docs({ action: \"apply\" })`\n"
  
  return preview
}

/**
 * Apply mode - execute edits
 */
async function applyMode(db, worktree, context, files, syncId) {
  let query = 'SELECT * FROM pending_syncs WHERE status = ?'
  let params = ['pending']
  
  if (syncId) {
    query += ' AND id = ?'
    params.push(syncId)
  }
  
  const pending = db.prepare(query).all(...params)
  
  if (pending.length === 0) {
    return "✅ No pending syncs to apply."
  }
  
  const results = []
  const modifiedFiles = new Set()
  
  for (const sync of pending) {
    let targets = db.prepare(`
      SELECT * FROM sync_targets WHERE sync_id = ?
    `).all(sync.id)
    
    // Filter by files if specified
    if (files && files.length > 0) {
      targets = targets.filter(t => files.includes(t.target_file))
    }
    
    // Group by file
    const targetsByFile = {}
    for (const target of targets) {
      if (!targetsByFile[target.target_file]) {
        targetsByFile[target.target_file] = []
      }
      targetsByFile[target.target_file].push(target)
    }
    
    // Apply edits file by file
    for (const [file, fileTargets] of Object.entries(targetsByFile)) {
      try {
        const filePath = `${worktree}/${file}`
        let content = readFileSync(filePath, 'utf-8')
        
        // Sort by line number descending to avoid offset issues
        fileTargets.sort((a, b) => (b.target_line || 0) - (a.target_line || 0))
        
        let editCount = 0
        for (const target of fileTargets) {
          // Simple string replacement
          // In production, would use more sophisticated line-based editing
          if (target.old_content && target.new_content) {
            if (content.includes(target.old_content)) {
              content = content.replace(target.old_content, target.new_content)
              editCount++
            }
          }
        }
        
        if (editCount > 0) {
          // Write back to file
          writeFileSync(filePath, content, 'utf-8')
          modifiedFiles.add(file)
          
          results.push({
            file,
            status: 'success',
            edits: editCount
          })
        } else {
          results.push({
            file,
            status: 'skipped',
            reason: 'No matching content found (file may have been manually edited)'
          })
        }
        
      } catch (error) {
        results.push({
          file,
          status: 'failed',
          error: error.message
        })
      }
    }
    
    // Mark sync as applied
    db.prepare(`
      UPDATE pending_syncs 
      SET status = 'applied', applied_at = ?
      WHERE id = ?
    `).run(Date.now(), sync.id)
    
    // Record in applied_syncs history
    db.prepare(`
      INSERT INTO applied_syncs (id, trigger_file, applied_at, files_modified, changes_summary)
      VALUES (?, ?, ?, ?, ?)
    `).run(
      sync.id,
      sync.trigger_file,
      Date.now(),
      JSON.stringify([...modifiedFiles]),
      sync.trigger_changes
    )
  }
  
  // Format report
  let report = "## Sync Applied\n\n"
  
  for (const result of results) {
    if (result.status === 'success') {
      report += `✅ **${result.file}**: Updated (${result.edits} edit(s))\n`
    } else if (result.status === 'skipped') {
      report += `⚠️ **${result.file}**: Skipped - ${result.reason}\n`
    } else if (result.status === 'failed') {
      report += `❌ **${result.file}**: Failed - ${result.error}\n`
    }
  }
  
  report += `\n**Total:** ${modifiedFiles.size} file(s) modified\n`
  
  if (syncId) {
    report += `Sync ID: ${syncId} marked as applied\n`
  } else {
    report += `${pending.length} sync(s) marked as applied\n`
  }
  
  report += "\nRun `git diff` to review changes.\n"
  
  return report
}

/**
 * Tool definition
 */
export default tool({
  description: "Check and synchronize documentation files after metadata changes (skill counts, versions, components, requirements). Detects inconsistencies and proposes/applies fixes.",
  
  args: {
    action: tool.schema.enum(["check", "preview", "apply"]).describe(
      "check: Show pending syncs | preview: Show line-by-line diffs | apply: Execute edits"
    ),
    
    files: tool.schema.array(tool.schema.string()).optional().describe(
      "Target specific files (e.g., ['AGENTS.md', 'MANUAL.md']). If omitted, affects all pending syncs."
    ),
    
    syncId: tool.schema.string().optional().describe(
      "Apply specific sync by ID (from check output). If omitted, applies all pending syncs."
    )
  },
  
  async execute(args, context) {
    const { worktree } = context
    const db = getDatabase(worktree)
    
    try {
      switch (args.action) {
        case "check":
          return checkMode(db, worktree)
        
        case "preview":
          return previewMode(db, worktree, args.files, args.syncId)
        
        case "apply":
          return await applyMode(db, worktree, context, args.files, args.syncId)
        
        default:
          return "❌ Invalid action. Use: check, preview, or apply"
      }
    } catch (error) {
      return `❌ Error: ${error.message}\n\nStack trace:\n${error.stack}`
    }
  }
})
