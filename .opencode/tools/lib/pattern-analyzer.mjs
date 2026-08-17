/**
 * Pattern Analyzer
 * 
 * Core logic for analyzing detected patterns and generating skill suggestions.
 * Used by skill-suggester skill to present patterns to users.
 */

import { readFileSync } from 'fs'
import { join } from 'path'

/**
 * Get all pending patterns from database
 */
export function getPendingPatterns(db) {
  const patterns = db.prepare(`
    SELECT * FROM patterns
    WHERE status = 'pending'
    ORDER BY confidence DESC, last_detected DESC
  `).all()

  // Get history for each pattern
  for (const pattern of patterns) {
    pattern.history = db.prepare(`
      SELECT * FROM pattern_history
      WHERE pattern_id = ?
      ORDER BY timestamp DESC
      LIMIT 10
    `).all(pattern.id)

    // Check if declined
    const declined = db.prepare(`
      SELECT * FROM declined_patterns
      WHERE pattern_id = ? AND can_suggest_again = 0
    `).get(pattern.id)

    pattern.isDeclined = !!declined
    pattern.cooldownUntil = declined?.cooldown_until || null
  }

  return patterns.filter(p => !p.isDeclined)
}

/**
 * Get all declined patterns (for review)
 */
export function getDeclinedPatterns(db) {
  return db.prepare(`
    SELECT p.*, dp.declined_at, dp.reason, dp.cooldown_until, dp.can_suggest_again
    FROM patterns p
    JOIN declined_patterns dp ON p.id = dp.pattern_id
    ORDER BY dp.declined_at DESC
  `).all()
}

/**
 * Calculate pattern confidence
 */
export function calculateConfidence(pattern) {
  let confidence = 0.4 // Base

  // More occurrences = higher confidence
  confidence += Math.min(0.3, pattern.occurrences * 0.1)

  // More sessions = higher confidence
  const sessionCount = new Set(pattern.history?.map(h => h.session_id) || []).size
  confidence += Math.min(0.2, sessionCount * 0.05)

  // Recent activity = higher confidence
  const lastDetected = pattern.last_detected
  const hoursAgo = (Date.now() - lastDetected) / (1000 * 60 * 60)
  if (hoursAgo < 24) confidence += 0.1

  return Math.min(0.95, confidence)
}

/**
 * Build grounding for a pattern (map to FR/COMP/TASK)
 */
export function buildGrounding(pattern, worktree) {
  const grounding = {
    documents: [],
    rules: []
  }

  // Try to map pattern to project documents
  const desc = pattern.description.toLowerCase()

  if (desc.includes('sync') || desc.includes('documentation')) {
    grounding.documents.push('WORKFLOW.md §6 Change Management')
    grounding.rules.push('Cross-document consistency required after metadata changes')
  }

  if (desc.includes('test')) {
    grounding.documents.push('AGENTS.md §Definition of Done')
    grounding.rules.push('Test must validate acceptance criteria from FR/US')
  }

  if (desc.includes('adr') || desc.includes('decision')) {
    grounding.documents.push('AGENTS.md §Guardrails')
    grounding.rules.push('Technical decisions must be documented as ADR')
  }

  if (desc.includes('task') || desc.includes('breakdown')) {
    grounding.documents.push('WORKFLOW.md §2 Fase 3 Planning')
    grounding.rules.push('Tasks must reference FR/COMP and be ≤1 day effort')
  }

  if (desc.includes('spec') || desc.includes('consistency')) {
    grounding.documents.push('ARCHITECTURE.md §4 Component mapping')
    grounding.rules.push('FR→COMP→TASK traceability must be maintained')
  }

  // If no specific mapping found, try to find FR/COMP references in actions
  if (grounding.documents.length === 0) {
    grounding.documents.push('AGENTS.md §Skills Available')
    grounding.rules.push('Pattern detected from repetitive user actions')
  }

  return grounding
}

/**
 * Generate skill draft from pattern
 */
export function generateSkillDraft(pattern, worktree) {
  const grounding = buildGrounding(pattern, worktree)

  // Generate skill name from pattern
  const skillName = generateSkillName(pattern)

  // Generate description with triggers
  const description = generateDescription(pattern)

  // Generate skill body
  const body = generateSkillBody(pattern, grounding)

  return {
    name: skillName,
    description,
    frontmatter: `---
name: ${skillName}
description: ${description}
license: MIT
compatibility: opencode
---`,
    body,
    grounding
  }
}

/**
 * Generate skill name from pattern
 */
function generateSkillName(pattern) {
  const desc = pattern.description.toLowerCase()

  // Map common patterns to skill names
  if (desc.includes('sync') && desc.includes('doc')) return 'doc-sync-automation'
  if (desc.includes('test') && desc.includes('migration')) return 'migration-test-helper'
  if (desc.includes('adr')) return 'adr-workflow'
  if (desc.includes('task') && desc.includes('breakdown')) return 'task-breakdown-assist'
  if (desc.includes('spec') && desc.includes('sync')) return 'spec-sync-validator'

  // Generic: use pattern name
  const cleanName = pattern.name
    .replace(/^pattern-/, '')
    .replace(/[^a-z0-9-]/g, '-')
    .replace(/-+/g, '-')
    .substring(0, 40)

  return cleanName || 'auto-detected-skill'
}

/**
 * Generate description with trigger keywords
 */
function generateDescription(pattern) {
  const desc = pattern.description
  const occurrences = pattern.occurrences

  return `Auto-suggested from ${occurrences} detected occurrences. ${desc}. Trigger: detected pattern from user actions.`
}

/**
 * Generate skill body
 */
function generateSkillBody(pattern, grounding) {
  let body = `# ${pattern.name}\n\n`

  body += `## Tujuan\n\n`
  body += `${pattern.description}.\n`
  body += `This skill was auto-suggested based on ${pattern.occurrences} detected occurrences.\n\n`

  body += `## Dasar/Rujukan\n\n`
  for (const doc of grounding.documents) {
    body += `- **${doc}**\n`
  }
  body += `\n`

  body += `## Langkah-Langkah\n\n`
  body += `1. [TODO: Define step-by-step procedure based on observed pattern]\n`
  body += `2. [TODO: Add acceptance criteria]\n`
  body += `3. [TODO: Add escalation conditions]\n\n`

  body += `## Kondisi Berhenti / Eskalasi\n\n`
  body += `- [TODO: Define when to stop and ask user]\n\n`

  body += `## Pattern Evidence\n\n`
  body += `| Session | Timestamp | Actions |\n`
  body += `|---------|-----------|--------|\n`

  if (pattern.history) {
    for (const h of pattern.history.slice(0, 5)) {
      const date = new Date(h.timestamp).toLocaleString()
      body += `| ${h.session_id.substring(0, 8)} | ${date} | ${h.actions ? 'See log' : 'N/A'} |\n`
    }
  }

  return body
}

/**
 * Check if pattern is in cooldown
 */
export function isPatternInCooldown(db, patternId) {
  const declined = db.prepare(`
    SELECT * FROM declined_patterns
    WHERE pattern_id = ? AND can_suggest_again = 0
  `).get(patternId)

  if (!declined) return false

  if (Date.now() >= declined.cooldown_until) {
    db.prepare(`
      UPDATE declined_patterns SET can_suggest_again = 1 WHERE pattern_id = ?
    `).run(patternId)
    return false
  }

  return true
}

/**
 * Decline a pattern suggestion
 */
export function declinePattern(db, patternId, reason) {
  const cooldownUntil = Date.now() + (30 * 24 * 60 * 60 * 1000) // 30 days

  db.prepare(`
    INSERT OR REPLACE INTO declined_patterns (pattern_id, declined_at, reason, cooldown_until, can_suggest_again)
    VALUES (?, ?, ?, ?, 0)
  `).run(patternId, Date.now(), reason || 'User declined', cooldownUntil)

  db.prepare(`
    UPDATE patterns SET status = 'declined', updated_at = ?
    WHERE id = ?
  `).run(Date.now(), patternId)

  return { cooldownUntil: new Date(cooldownUntil).toLocaleString() }
}

/**
 * Mark pattern as created (skill was created)
 */
export function markPatternCreated(db, patternId) {
  db.prepare(`
    UPDATE patterns SET status = 'created', updated_at = ?
    WHERE id = ?
  `).run(Date.now(), patternId)
}

/**
 * Format pattern for display
 */
export function formatPattern(pattern, worktree) {
  let output = `## Pattern: ${pattern.name}\n\n`

  output += `**Description:** ${pattern.description}\n`
  output += `**Occurrences:** ${pattern.occurrences}\n`
  output += `**Confidence:** ${(pattern.confidence * 100).toFixed(0)}%\n`
  output += `**First detected:** ${new Date(pattern.first_detected).toLocaleString()}\n`
  output += `**Last detected:** ${new Date(pattern.last_detected).toLocaleString()}\n`
  output += `**Pattern ID:** ${pattern.id}\n\n`

  if (pattern.history && pattern.history.length > 0) {
    output += `### Evidence (last ${Math.min(pattern.history.length, 5)} occurrences):\n\n`
    output += `| Session | Timestamp |\n`
    output += `|---------|-----------|\n`

    for (const h of pattern.history.slice(0, 5)) {
      const date = new Date(h.timestamp).toLocaleString()
      output += `| ${h.session_id.substring(0, 8)}... | ${date} |\n`
    }
    output += `\n`
  }

  const grounding = buildGrounding(pattern, worktree)
  output += `### Grounding:\n`
  for (const doc of grounding.documents) {
    output += `- ${doc}\n`
  }
  output += `\n`

  const draft = generateSkillDraft(pattern, worktree)
  output += `### Suggested Skill Draft:\n\n`
  output += `\`\`\`yaml\n${draft.frontmatter}\n\`\`\`\n\n`

  return output
}
