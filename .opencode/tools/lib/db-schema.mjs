/**
 * SQLite Database Schema for Doc Sync System
 * 
 * Tables:
 * - file_hashes: Track file content hashes to detect actual changes
 * - pending_syncs: Store detected sync needs awaiting user action
 * - applied_syncs: Historical record of completed syncs
 * - session_actions: Log user actions (tool calls, prompts) for pattern tracking
 * - patterns: Detected repetitive patterns
 * - pattern_history: Individual pattern occurrences with timestamps
 * - declined_patterns: User-declined skill suggestions with cooldown
 */

import { Database } from 'bun:sqlite'
import { join } from 'path'

export function initializeSchema(db) {
  // Enable foreign keys (Bun SQLite uses exec() instead of pragma())
  db.exec('PRAGMA foreign_keys = ON')
  
  // Table: file_hashes
  db.exec(`
    CREATE TABLE IF NOT EXISTS file_hashes (
      file TEXT PRIMARY KEY,
      hash TEXT NOT NULL,
      last_metadata TEXT,
      updated_at INTEGER NOT NULL,
      created_at INTEGER NOT NULL DEFAULT (strftime('%s', 'now') * 1000)
    )
  `)
  
  // Table: pending_syncs
  db.exec(`
    CREATE TABLE IF NOT EXISTS pending_syncs (
      id TEXT PRIMARY KEY,
      trigger_file TEXT NOT NULL,
      trigger_changes TEXT NOT NULL,
      detected_at INTEGER NOT NULL,
      status TEXT NOT NULL DEFAULT 'pending',
      confidence REAL NOT NULL DEFAULT 0.0,
      declined_at INTEGER,
      applied_at INTEGER
    )
  `)
  
  // Table: sync_targets
  db.exec(`
    CREATE TABLE IF NOT EXISTS sync_targets (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      sync_id TEXT NOT NULL,
      target_file TEXT NOT NULL,
      target_line INTEGER,
      old_content TEXT,
      new_content TEXT,
      confidence REAL NOT NULL DEFAULT 0.0,
      FOREIGN KEY (sync_id) REFERENCES pending_syncs(id) ON DELETE CASCADE
    )
  `)
  
  // Table: applied_syncs (historical record)
  db.exec(`
    CREATE TABLE IF NOT EXISTS applied_syncs (
      id TEXT PRIMARY KEY,
      trigger_file TEXT NOT NULL,
      applied_at INTEGER NOT NULL,
      files_modified TEXT NOT NULL,
      changes_summary TEXT
    )
  `)
  
  // === Phase 2: Pattern Tracking Tables ===
  
  // Table: session_actions
  db.exec(`
    CREATE TABLE IF NOT EXISTS session_actions (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      session_id TEXT NOT NULL,
      action_type TEXT NOT NULL,
      action_data TEXT,
      timestamp INTEGER NOT NULL,
      created_at INTEGER NOT NULL DEFAULT (strftime('%s', 'now') * 1000)
    )
  `)
  
  // Table: patterns
  db.exec(`
    CREATE TABLE IF NOT EXISTS patterns (
      id TEXT PRIMARY KEY,
      name TEXT NOT NULL UNIQUE,
      description TEXT NOT NULL,
      occurrences INTEGER NOT NULL DEFAULT 0,
      confidence REAL NOT NULL DEFAULT 0.0,
      first_detected INTEGER NOT NULL,
      last_detected INTEGER NOT NULL,
      grounding TEXT,
      status TEXT NOT NULL DEFAULT 'pending',
      created_at INTEGER NOT NULL DEFAULT (strftime('%s', 'now') * 1000),
      updated_at INTEGER NOT NULL DEFAULT (strftime('%s', 'now') * 1000)
    )
  `)
  
  // Table: pattern_history
  db.exec(`
    CREATE TABLE IF NOT EXISTS pattern_history (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      pattern_id TEXT NOT NULL,
      session_id TEXT NOT NULL,
      actions TEXT,
      timestamp INTEGER NOT NULL,
      notes TEXT,
      FOREIGN KEY (pattern_id) REFERENCES patterns(id) ON DELETE CASCADE
    )
  `)
  
  // Table: declined_patterns
  db.exec(`
    CREATE TABLE IF NOT EXISTS declined_patterns (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      pattern_id TEXT NOT NULL UNIQUE,
      declined_at INTEGER NOT NULL,
      reason TEXT,
      cooldown_until INTEGER NOT NULL,
      can_suggest_again INTEGER NOT NULL DEFAULT 0,
      FOREIGN KEY (pattern_id) REFERENCES patterns(id) ON DELETE CASCADE
    )
  `)
  
  // Indexes for performance
  db.exec(`
    CREATE INDEX IF NOT EXISTS idx_pending_syncs_status 
    ON pending_syncs(status);
    
    CREATE INDEX IF NOT EXISTS idx_pending_syncs_trigger 
    ON pending_syncs(trigger_file);
    
    CREATE INDEX IF NOT EXISTS idx_sync_targets_sync_id 
    ON sync_targets(sync_id);
    
    CREATE INDEX IF NOT EXISTS idx_applied_syncs_trigger 
    ON applied_syncs(trigger_file);
    
    CREATE INDEX IF NOT EXISTS idx_session_actions_session 
    ON session_actions(session_id);
    
    CREATE INDEX IF NOT EXISTS idx_session_actions_type 
    ON session_actions(action_type);
    
    CREATE INDEX IF NOT EXISTS idx_patterns_status 
    ON patterns(status);
    
    CREATE INDEX IF NOT EXISTS idx_pattern_history_pattern 
    ON pattern_history(pattern_id);
    
    CREATE INDEX IF NOT EXISTS idx_declined_patterns_cooldown 
    ON declined_patterns(cooldown_until);
  `)
  
  console.log('[doc-sync] Database schema initialized (Phase 1 + Phase 2)')
}

/**
 * Generate unique sync ID
 */
export function generateSyncId() {
  const timestamp = Date.now().toString(36)
  const random = Math.random().toString(36).substring(2, 8)
  return `sync-${timestamp}-${random}`
}

/**
 * Generate unique pattern ID
 */
export function generatePatternId() {
  const timestamp = Date.now().toString(36)
  const random = Math.random().toString(36).substring(2, 6)
  return `pattern-${timestamp}-${random}`
}

/**
 * Get database instance (singleton pattern)
 */
let dbInstance = null

export function getDatabase(worktree) {
  if (!dbInstance) {
    const dbPath = join(worktree, '.opencode', 'doc-sync.db')
    dbInstance = new Database(dbPath)
    
    // Initialize schema if needed
    initializeSchema(dbInstance)
  }
  
  return dbInstance
}

/**
 * Close database connection
 */
export function closeDatabase() {
  if (dbInstance) {
    dbInstance.close()
    dbInstance = null
  }
}
