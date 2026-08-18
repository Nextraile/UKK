/**
 * Sync Planner
 * 
 * Determines which files need to be synced based on detected changes.
 * Maps trigger file changes to affected target files with confidence scores.
 */

/**
 * Sync mapping rules
 * 
 * Defines which files need sync when a trigger file changes
 */
const SYNC_RULES = {
  'SKILL.md': {
    'skillCount': [
      { target: 'AGENTS.md', pattern: /(\d+)\s+skills?\s+in/, confidence: 0.95 },
      { target: 'MANUAL.md', pattern: /(\d+)\s+skills?\s+ter-install/, confidence: 0.90 }
    ],
    'version': [
      { target: 'AGENTS.md', pattern: /Last updated:\s*(\d{4}-\d{2}-\d{2})/, confidence: 0.85 }
    ],
    'skills': [
      { target: 'AGENTS.md', section: '## Skills Available', confidence: 0.90 }
    ]
  },
  
  'ARCHITECTURE.md': {
    'version': [
      { target: 'PRD.md', section: 'changelog', confidence: 0.70, conditional: true }
    ],
    'components': [
      { target: 'TODO.md', validate: 'spec-sync', confidence: 0.95 }
    ],
    'adrs': [
      { target: 'AGENTS.md', conditional: true, confidence: 0.60 }
    ]
  },
  
  'PRD.md': {
    'version': [
      { target: 'ARCHITECTURE.md', section: 'changelog', confidence: 0.70, conditional: true }
    ],
    'requirements': [
      { target: 'ARCHITECTURE.md', validate: 'spec-sync', confidence: 0.95 },
      { target: 'TODO.md', validate: 'spec-sync', confidence: 0.95 }
    ]
  },
  
  'TODO.md': {
    // TODO changes don't trigger other doc syncs
  },
  
  'WORKFLOW.md': {
    // WORKFLOW changes don't trigger other doc syncs
  },
  
  'AGENTS.md': {
    // AGENTS.md is target, not trigger
  },
  
  'MANUAL.md': {
    // MANUAL.md is target, not trigger
  }
}

/**
 * Determine sync targets based on trigger file and changes
 */
export function determineSyncTargets(triggerFile, changes) {
  const targets = []
  const rules = SYNC_RULES[triggerFile]
  
  if (!rules) return targets
  
  for (const change of changes) {
    const ruleSet = rules[change.type]
    
    if (!ruleSet) continue
    
    for (const rule of ruleSet) {
      // Skip conditional syncs (require manual review)
      if (rule.conditional && !shouldApplyConditionalSync(triggerFile, change)) {
        continue
      }
      
      // Skip if requires validation that hasn't been run yet
      if (rule.validate === 'spec-sync') {
        targets.push({
          target: rule.target,
          changeType: change.type,
          change: change,
          confidence: rule.confidence,
          requiresValidation: 'spec-sync'
        })
        continue
      }
      
      targets.push({
        target: rule.target,
        changeType: change.type,
        change: change,
        pattern: rule.pattern,
        section: rule.section,
        confidence: rule.confidence
      })
    }
  }
  
  return targets
}

/**
 * Check if conditional sync should apply
 */
function shouldApplyConditionalSync(triggerFile, change) {
  // Conditional syncs are conservative - only apply if change is significant
  
  if (triggerFile === 'ARCHITECTURE.md' && change.type === 'adrs') {
    // ADR additions might affect AGENTS.md if they change setup/commands
    // This is too context-dependent, so mark as conditional (manual review)
    return false
  }
  
  if (change.type === 'version') {
    // Version changes rarely require cross-doc updates unless major change
    // Mark as conditional
    return false
  }
  
  return false
}

/**
 * Generate sync plan from detected changes
 */
export async function generateSyncPlan(triggerFile, changes, currentMetadata) {
  const targets = determineSyncTargets(triggerFile, changes)
  
  const plan = {
    triggerFile,
    changes,
    targets: [],
    requiresSpecSync: false,
    timestamp: Date.now()
  }
  
  // Check if spec-sync validation required
  plan.requiresSpecSync = targets.some(t => t.requiresValidation === 'spec-sync')
  
  // For each target, generate specific edit proposals
  for (const target of targets) {
    const edits = await generateEditsForTarget(
      target.target,
      target.changeType,
      target.change,
      target.pattern,
      currentMetadata
    )
    
    if (edits.length > 0) {
      plan.targets.push({
        file: target.target,
        changeType: target.changeType,
        edits,
        confidence: target.confidence,
        requiresValidation: target.requiresValidation
      })
    }
  }
  
  return plan
}

/**
 * Generate specific edits for a target file
 */
async function generateEditsForTarget(targetFile, changeType, change, pattern, metadata) {
  const edits = []
  
  try {
    const { readFileSync } = await import('fs')
    const path = await import('path')
    
    // This is a simplified implementation
    // In production, would use more sophisticated line-by-line analysis
    
    if (changeType === 'skillCount') {
      const content = readFileSync(targetFile, 'utf-8')
      
      if (pattern) {
        const match = content.match(pattern)
        
        if (match) {
          const lineNumber = content.substring(0, match.index).split('\n').length
          const oldValue = match[1]
          const newValue = change.new
          
          edits.push({
            line: lineNumber,
            old: match[0],
            new: match[0].replace(oldValue, newValue.toString())
          })
        }
      }
    }
    
    // Add more edit generation logic for other change types...
    
  } catch (error) {
    console.warn(`[sync-planner] Failed to generate edits for ${targetFile}:`, error.message)
  }
  
  return edits
}

/**
 * Calculate overall confidence for sync plan
 */
export function calculatePlanConfidence(plan) {
  if (plan.targets.length === 0) return 0
  
  const confidences = plan.targets.map(t => t.confidence)
  const avgConfidence = confidences.reduce((a, b) => a + b, 0) / confidences.length
  
  // Penalize if spec-sync required but not validated yet
  if (plan.requiresSpecSync) {
    return avgConfidence * 0.8
  }
  
  return avgConfidence
}

/**
 * Format sync plan for display
 */
export function formatSyncPlan(plan) {
  let output = '## Documentation Sync Plan\n\n'
  
  output += `**Trigger:** ${plan.triggerFile}\n`
  output += `**Detected:** ${new Date(plan.timestamp).toLocaleString()}\n\n`
  
  if (plan.changes.length > 0) {
    output += '### Changes Detected:\n'
    for (const change of plan.changes) {
      if (change.type === 'version') {
        output += `- Version: ${change.old} → ${change.new}\n`
      } else if (change.type === 'skillCount') {
        output += `- Skill count: ${change.old} → ${change.new}\n`
      } else if (change.type === 'skills') {
        if (change.added.length > 0) {
          output += `- Skills added: ${change.added.join(', ')}\n`
        }
        if (change.removed.length > 0) {
          output += `- Skills removed: ${change.removed.join(', ')}\n`
        }
      } else if (change.type === 'components') {
        if (change.added.length > 0) {
          output += `- Components added: ${change.added.join(', ')}\n`
        }
        if (change.removed.length > 0) {
          output += `- Components removed: ${change.removed.join(', ')}\n`
        }
      }
    }
    output += '\n'
  }
  
  if (plan.requiresSpecSync) {
    output += '⚠️ **spec-sync validation required** (new COMPs or FRs detected)\n\n'
  }
  
  if (plan.targets.length > 0) {
    output += '### Affected Files:\n'
    for (const target of plan.targets) {
      const emoji = target.confidence >= 0.9 ? '✅' : target.confidence >= 0.7 ? '⚠️' : '❓'
      output += `${emoji} **${target.file}** (confidence: ${(target.confidence * 100).toFixed(0)}%)\n`
      
      for (const edit of target.edits) {
        output += `   - Line ${edit.line}: "${edit.old}" → "${edit.new}"\n`
      }
    }
    output += '\n'
  } else {
    output += '✅ No sync targets detected. Changes are isolated.\n\n'
  }
  
  const overallConfidence = calculatePlanConfidence(plan)
  
  if (overallConfidence >= 0.9) {
    output += '**Recommendation:** High confidence - safe to apply\n'
  } else if (overallConfidence >= 0.7) {
    output += '**Recommendation:** Medium confidence - review recommended\n'
  } else {
    output += '**Recommendation:** Low confidence - manual verification required\n'
  }
  
  return output
}
