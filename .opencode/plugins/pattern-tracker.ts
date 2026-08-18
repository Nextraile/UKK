/**
 * Pattern Tracker Plugin
 * 
 * Monitors user actions (tool calls, prompts, file edits) to detect
 * repetitive patterns that could justify creating a new skill.
 * 
 * Detection logic:
 * 1. Track all tool calls in session → action log
 * 2. At session.idle → analyze for repetition (≥3x)
 * 3. Calculate confidence (occurrence rate, time span)
 * 4. Store pending pattern in SQLite
 * 5. Show toast if high confidence
 */

import { getDatabase, generatePatternId } from "../tools/lib/db-schema.mjs"

/**
 * Actions that are tracked for pattern detection
 */
const TRACKED_TOOLS = [
  'write', 'edit', 'read', 'bash',
  'skill', 'glob', 'grep', 'task'
]

/**
 * Skill invocations that are tracked
 */
const TRACKED_SKILLS = [
  'spec-sync', 'task-breakdown', 'adr-writer',
  'doc-consistency-check', 'test-writer', 'docs-lookup',
  'doc-sync-checker'
]

/**
 * Log a session action
 */
function logAction(db, sessionId, actionType, actionData) {
  db.prepare(`
    INSERT INTO session_actions (session_id, action_type, action_data, timestamp)
    VALUES (?, ?, ?, ?)
  `).run(sessionId, actionType, JSON.stringify(actionData), Date.now())
}

/**
 * Analyze actions for repetitive patterns
 */
function analyzePatterns(db, sessionId) {
  // Get all actions from current session
  const actions = db.prepare(`
    SELECT * FROM session_actions
    WHERE session_id = ?
    ORDER BY timestamp ASC
  `).all(sessionId)

  if (actions.length < 3) return []

  const patterns = []

  // Detect: Tool call sequences (e.g., edit → edit → edit same type of file)
  const toolSequences = detectToolSequences(actions)
  patterns.push(...toolSequences)

  // Detect: Skill invocations (e.g., calling spec-sync 3+ times)
  const skillPatterns = detectSkillPatterns(actions)
  patterns.push(...skillPatterns)

  // Detect: File edit patterns (e.g., editing same set of files repeatedly)
  const fileEditPatterns = detectFileEditPatterns(actions)
  patterns.push(...fileEditPatterns)

  // Cross-session: Check if patterns from previous sessions match
  const crossSessionPatterns = detectCrossSessionPatterns(db, sessionId, patterns)
  patterns.push(...crossSessionPatterns)

  return patterns
}

/**
 * Detect repetitive tool call sequences
 */
function detectToolSequences(actions) {
  const patterns = []
  const sequences = new Map()

  // Group actions into sequences of 3
  for (let i = 0; i <= actions.length - 3; i++) {
    const seq = actions.slice(i, i + 3)
    const key = seq.map(a => {
      const data = JSON.parse(a.action_data || '{}')
      return `${a.action_type}:${data.tool || data.file || 'unknown'}`
    }).join(' → ')

    if (!sequences.has(key)) {
      sequences.set(key, { count: 0, actions: seq })
    }
    sequences.get(key).count++
  }

  // Find sequences that repeat 3+ times
  for (const [key, data] of sequences) {
    if (data.count >= 3) {
      patterns.push({
        type: 'tool_sequence',
        name: key.substring(0, 100),
        description: `Repeated tool sequence: ${key}`,
        occurrences: data.count,
        confidence: Math.min(0.95, 0.5 + (data.count * 0.15)),
        actions: data.actions
      })
    }
  }

  return patterns
}

/**
 * Detect repetitive skill invocations
 */
function detectSkillPatterns(actions) {
  const patterns = []
  const skillCounts = new Map()

  for (const action of actions) {
    if (action.action_type !== 'skill') continue

    const data = JSON.parse(action.action_data || '{}')
    const skillName = data.skill

    if (!skillName) continue

    if (!skillCounts.has(skillName)) {
      skillCounts.set(skillName, { count: 0, sessions: new Set(), actions: [] })
    }

    const entry = skillCounts.get(skillName)
    entry.count++
    entry.sessions.add(action.session_id)
    entry.actions.push(action)
  }

  // Find skills called 3+ times
  for (const [skillName, data] of skillCounts) {
    if (data.count >= 3) {
      patterns.push({
        type: 'skill_invocation',
        name: `pattern-skill-${skillName}`,
        description: `Skill \`${skillName}\` invoked ${data.count} times`,
        occurrences: data.count,
        confidence: Math.min(0.95, 0.6 + (data.count * 0.1)),
        actions: data.actions
      })
    }
  }

  return patterns
}

/**
 * Detect repetitive file edit patterns
 */
function detectFileEditPatterns(actions) {
  const patterns = []
  const fileEdits = new Map()

  for (const action of actions) {
    if (action.action_type !== 'file_edit') continue

    const data = JSON.parse(action.action_data || '{}')
    const file = data.file

    if (!file) continue

    if (!fileEdits.has(file)) {
      fileEdits.set(file, { count: 0, actions: [] })
    }

    const entry = fileEdits.get(file)
    entry.count++
    entry.actions.push(action)
  }

  // Find files edited 3+ times
  for (const [file, data] of fileEdits) {
    if (data.count >= 3) {
      patterns.push({
        type: 'file_edit',
        name: `pattern-edit-${file.replace(/[^a-z0-9]/gi, '-').toLowerCase()}`,
        description: `File \`${file}\` edited ${data.count} times`,
        occurrences: data.count,
        confidence: Math.min(0.90, 0.5 + (data.count * 0.1)),
        actions: data.actions
      })
    }
  }

  return patterns
}

/**
 * Check for cross-session patterns
 */
function detectCrossSessionPatterns(db, currentSessionId, currentPatterns) {
  const crossSession = []

  for (const pattern of currentPatterns) {
    // Check if similar pattern exists in previous sessions
    const existing = db.prepare(`
      SELECT * FROM patterns
      WHERE name = ? AND status = 'pending'
    `).get(pattern.name)

    if (existing) {
      // Pattern already tracked, update occurrence count
      const newCount = existing.occurrences + 1
      const newConfidence = Math.min(0.95, existing.confidence + 0.05)

      db.prepare(`
        UPDATE patterns
        SET occurrences = ?, confidence = ?, last_detected = ?, updated_at = ?
        WHERE id = ?
      `).run(newCount, newConfidence, Date.now(), Date.now(), existing.id)

      // Add to pattern history
      db.prepare(`
        INSERT INTO pattern_history (pattern_id, session_id, actions, timestamp)
        VALUES (?, ?, ?, ?)
      `).run(
        existing.id,
        currentSessionId,
        JSON.stringify(pattern.actions.map(a => a.id)),
        Date.now()
      )

      crossSession.push({
        ...pattern,
        id: existing.id,
        occurrences: newCount,
        confidence: newConfidence,
        crossSession: true
      })
    } else {
      // New pattern, check if meets threshold
      if (pattern.confidence >= 0.7) {
        const patternId = generatePatternId()

        db.prepare(`
          INSERT INTO patterns (id, name, description, occurrences, confidence, first_detected, last_detected, status)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        `).run(
          patternId,
          pattern.name,
          pattern.description,
          pattern.occurrences,
          pattern.confidence,
          Date.now(),
          Date.now(),
          'pending'
        )

        // Add to pattern history
        db.prepare(`
          INSERT INTO pattern_history (pattern_id, session_id, actions, timestamp)
          VALUES (?, ?, ?, ?)
        `).run(
          patternId,
          currentSessionId,
          JSON.stringify(pattern.actions.map(a => a.id)),
          Date.now()
        )

        crossSession.push({
          ...pattern,
          id: patternId,
          crossSession: false
        })
      }
    }
  }

  return crossSession
}

/**
 * Check if pattern is in cooldown (declined)
 */
function isPatternInCooldown(db, patternId) {
  const declined = db.prepare(`
    SELECT * FROM declined_patterns
    WHERE pattern_id = ? AND can_suggest_again = 0
  `).get(patternId)

  if (!declined) return false

  // Check if cooldown has expired
  if (Date.now() >= declined.cooldown_until) {
    db.prepare(`
      UPDATE declined_patterns SET can_suggest_again = 1 WHERE pattern_id = ?
    `).run(patternId)
    return false
  }

  return true
}

/**
 * Plugin definition
 */
export const PatternTracker = async ({ client, worktree }) => {
  console.log('[pattern-tracker] Plugin initializing...')

  const db = getDatabase(worktree)
  let currentSessionId = null

  return {
    /**
     * Session created - initialize tracking
     */
    "session.created": async (input, output) => {
      currentSessionId = input.sessionID || `sess-${Date.now()}`
      console.log(`[pattern-tracker] Session started: ${currentSessionId}`)
    },

    /**
     * Tool execution - log tool calls
     */
    "tool.execute.after": async (input, output) => {
      if (!currentSessionId) return

      const { tool } = input

      // Only track relevant tools
      if (!TRACKED_TOOLS.includes(tool)) return

      const actionData = {
        tool,
        args: output?.args ? Object.keys(output.args) : []
      }

      logAction(db, currentSessionId, 'tool_call', actionData)
    },

    /**
     * Message updated - capture user prompts
     */
    "message.updated": async (input, output) => {
      if (!currentSessionId) return

      const { role, content } = input

      // Only track user messages (not assistant)
      if (role !== 'user') return

      // Only log if there's actual content
      if (!content || content.length < 10) return

      const actionData = {
        prompt_length: content.length,
        prompt_preview: content.substring(0, 100),
        keywords: extractKeywords(content)
      }

      logAction(db, currentSessionId, 'prompt', actionData)
    },

    /**
     * Session idle - analyze for patterns
     */
    "session.idle": async (input, output) => {
      if (!currentSessionId) return

      console.log('[pattern-tracker] Session idle, analyzing patterns...')

      const patterns = analyzePatterns(db, currentSessionId)

      if (patterns.length === 0) {
        console.log('[pattern-tracker] No patterns detected')
        return
      }

      // Filter out declined patterns
      const activePatterns = patterns.filter(p => {
        if (p.id) {
          return !isPatternInCooldown(db, p.id)
        }
        return true
      })

      if (activePatterns.length === 0) {
        console.log('[pattern-tracker] All detected patterns are in cooldown')
        return
      }

      // Show toast for high-confidence patterns
      const highConfidencePatterns = activePatterns.filter(p => p.confidence >= 0.7)

      if (highConfidencePatterns.length > 0) {
        const message = `💡 Pattern detected: ${highConfidencePatterns[0].description}. Run skill-suggester to review.`

        try {
          await client.tui.toast.show({
            body: {
              message,
              duration: 8000
            }
          })
          console.log(`[pattern-tracker] Toast shown: ${message}`)
        } catch (error) {
          console.warn('[pattern-tracker] Failed to show toast:', error.message)
        }
      }

      console.log(`[pattern-tracker] ${activePatterns.length} pattern(s) detected`)
    }
  }
}

/**
 * Extract keywords from user prompt
 */
function extractKeywords(text) {
  const keywords = []

  // Detect skill-related keywords
  const skillKeywords = ['skill', 'sync', 'test', 'adr', 'docs', 'spec', 'task', 'pattern']
  for (const kw of skillKeywords) {
    if (text.toLowerCase().includes(kw)) {
      keywords.push(kw)
    }
  }

  // Detect file references
  const fileMatches = text.match(/[\w-]+\.md/g) || []
  keywords.push(...fileMatches)

  return keywords
}

export default PatternTracker
