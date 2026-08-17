/**
 * Documentation Parser
 * 
 * Extracts semantic metadata from core documentation files:
 * - SKILL.md: version, skill count, skill list
 * - ARCHITECTURE.md: version, components, ADRs, dependencies
 * - PRD.md: version, requirements, NFRs
 * - TODO.md: task count, completion rate
 * - WORKFLOW.md: version
 * - AGENTS.md: skill references, last updated
 * - MANUAL.md: skill references
 */

import { readFileSync } from 'fs'
import { join } from 'path'
import matter from 'gray-matter'

/**
 * Parse SKILL.md metadata
 */
export async function parseSkillMd(filePath) {
  const content = readFileSync(filePath, 'utf-8')
  
  // Extract version from changelog (last entry matching date format)
  const versionMatches = [...content.matchAll(/\|\s*(\d+\.\d+\.\d+)\s*\|\s*(\d{4}-\d{2}-\d{2})\s*\|/g)]
  const lastMatch = versionMatches[versionMatches.length - 1]
  const version = lastMatch ? lastMatch[1] : null
  const lastUpdated = lastMatch ? lastMatch[2] : null
  
  // Count skills in table (§1) — match rows with backtick-wrapped skill names
  const skillTableMatch = content.match(/## 1\.\s*Daftar Skill Proyek Ini([\s\S]*?)\n---/)
  let skillCount = 0
  const skills = []
  
  if (skillTableMatch) {
    const tableContent = skillTableMatch[1]
    // Match table rows: | `skill-name` | ... |
    const rows = tableContent.match(/^\|\s*`[^`]+`\s*\|/gm) || []
    skillCount = rows.length
    
    // Extract skill names
    rows.forEach(row => {
      const nameMatch = row.match(/`([^`]+)`/)
      if (nameMatch) skills.push(nameMatch[1])
    })
  }
  
  return {
    version,
    lastUpdated,
    skillCount,
    skills,
    file: 'SKILL.md'
  }
}

/**
 * Parse ARCHITECTURE.md metadata
 */
export async function parseArchitectureMd(filePath) {
  const content = readFileSync(filePath, 'utf-8')
  
  // Extract version from header table
  const versionMatch = content.match(/\|\s*Versi Dokumen\s*\|\s*[`"]?([\d.]+)[`"]?\s*\|/)
  const version = versionMatch ? versionMatch[1] : null
  
  // Extract components (COMP-xxx)
  const compMatches = content.matchAll(/COMP-(\d{3})/g)
  const components = [...new Set([...compMatches].map(m => `COMP-${m[1]}`))]
  
  // Extract ADRs (ADR-xxx)
  const adrMatches = content.matchAll(/ADR-(\d{3})/g)
  const adrs = [...new Set([...adrMatches].map(m => `ADR-${m[1]}`))]
  
  // Extract dependencies from tech stack table
  const dependencies = {}
  const depTableMatch = content.match(/##\s*3\.1.*?Rujukan Dokumentasi([\s\S]*?)---/)
  if (depTableMatch) {
    const rows = depTableMatch[1].matchAll(/\|\s*([^|]+)\s*\|\s*([\d.]+)/g)
    for (const row of rows) {
      const name = row[1].trim()
      const ver = row[2].trim()
      if (name && ver && !name.includes('---')) {
        dependencies[name] = ver
      }
    }
  }
  
  return {
    version,
    components,
    adrs,
    dependencies,
    file: 'ARCHITECTURE.md'
  }
}

/**
 * Parse PRD.md metadata
 */
export async function parsePrdMd(filePath) {
  const content = readFileSync(filePath, 'utf-8')
  
  // Extract version from header table
  const versionMatch = content.match(/\|\s*Versi Dokumen\s*\|\s*[`"]?([\d.]+)[`"]?\s*\|/)
  const version = versionMatch ? versionMatch[1] : null
  
  // Extract requirements (FR-xxx)
  const frMatches = content.matchAll(/FR-(\d{3})/g)
  const requirements = [...new Set([...frMatches].map(m => `FR-${m[1]}`))]
  
  // Extract NFRs (NFR-xxx)
  const nfrMatches = content.matchAll(/NFR-(\d{3})/g)
  const nfrs = [...new Set([...nfrMatches].map(m => `NFR-${m[1]}`))]
  
  return {
    version,
    requirements,
    nfrs,
    file: 'PRD.md'
  }
}

/**
 * Parse TODO.md metadata
 */
export async function parseTodoMd(filePath) {
  const content = readFileSync(filePath, 'utf-8')
  
  // Extract version from header table
  const versionMatch = content.match(/\|\s*Versi\s*\|\s*Tanggal.*?\|\s*(\d+\.\d+\.\d+)/)
  const version = versionMatch ? versionMatch[1] : null
  
  // Count tasks (TASK-xxx)
  const taskMatches = content.matchAll(/TASK-(\d{3})/g)
  const tasks = [...new Set([...taskMatches].map(m => `TASK-${m[1]}`))]
  const taskCount = tasks.length
  
  // Count completed tasks (Done status)
  const doneMatches = content.matchAll(/TASK-\d{3}.*?\|\s*Done\s*\|/g)
  const completedCount = [...doneMatches].length
  
  return {
    version,
    taskCount,
    completedCount,
    tasks,
    file: 'TODO.md'
  }
}

/**
 * Parse WORKFLOW.md metadata
 */
export async function parseWorkflowMd(filePath) {
  const content = readFileSync(filePath, 'utf-8')
  
  // Extract version from header table
  const versionMatch = content.match(/\|\s*Versi Dokumen\s*\|\s*([\d.]+)\s*\|/)
  const version = versionMatch ? versionMatch[1] : null
  
  const updatedMatch = content.match(/\|\s*Terakhir Diperbarui\s*\|\s*(\d{4}-\d{2}-\d{2})\s*\|/)
  const lastUpdated = updatedMatch ? updatedMatch[1] : null
  
  return {
    version,
    lastUpdated,
    file: 'WORKFLOW.md'
  }
}

/**
 * Parse AGENTS.md metadata
 */
export async function parseAgentsMd(filePath) {
  const content = readFileSync(filePath, 'utf-8')
  
  // Extract skill count
  const skillCountMatch = content.match(/(\d+)\s+skills?\s+in\s+`\.opencode\/skills\/`/)
  const skillCount = skillCountMatch ? parseInt(skillCountMatch[1]) : null
  
  // Extract last updated
  const updatedMatch = content.match(/Last updated:\s*(\d{4}-\d{2}-\d{2})/)
  const lastUpdated = updatedMatch ? updatedMatch[1] : null
  
  // Extract project status
  const statusMatch = content.match(/\*\*Project status:\*\*\s*([^.]+)/)
  const projectStatus = statusMatch ? statusMatch[1].trim() : null
  
  return {
    skillCount,
    lastUpdated,
    projectStatus,
    file: 'AGENTS.md'
  }
}

/**
 * Parse MANUAL.md metadata
 */
export async function parseManualMd(filePath) {
  const content = readFileSync(filePath, 'utf-8')
  
  // Extract skill reference
  const skillRefMatch = content.match(/(\d+)\s+skills?\s+ter-install/)
  const skillCount = skillRefMatch ? parseInt(skillRefMatch[1]) : null
  
  return {
    skillCount,
    file: 'MANUAL.md'
  }
}

/**
 * Parse all documentation files
 */
export async function parseAllDocs(worktree) {
  const results = {}
  
  try {
    results.skill = await parseSkillMd(join(worktree, 'SKILL.md'))
  } catch (e) {
    console.warn('[doc-parser] Failed to parse SKILL.md:', e.message)
  }
  
  try {
    results.architecture = await parseArchitectureMd(join(worktree, 'ARCHITECTURE.md'))
  } catch (e) {
    console.warn('[doc-parser] Failed to parse ARCHITECTURE.md:', e.message)
  }
  
  try {
    results.prd = await parsePrdMd(join(worktree, 'PRD.md'))
  } catch (e) {
    console.warn('[doc-parser] Failed to parse PRD.md:', e.message)
  }
  
  try {
    results.todo = await parseTodoMd(join(worktree, 'TODO.md'))
  } catch (e) {
    console.warn('[doc-parser] Failed to parse TODO.md:', e.message)
  }
  
  try {
    results.workflow = await parseWorkflowMd(join(worktree, 'WORKFLOW.md'))
  } catch (e) {
    console.warn('[doc-parser] Failed to parse WORKFLOW.md:', e.message)
  }
  
  try {
    results.agents = await parseAgentsMd(join(worktree, 'AGENTS.md'))
  } catch (e) {
    console.warn('[doc-parser] Failed to parse AGENTS.md:', e.message)
  }
  
  try {
    results.manual = await parseManualMd(join(worktree, 'MANUAL.md'))
  } catch (e) {
    console.warn('[doc-parser] Failed to parse MANUAL.md:', e.message)
  }
  
  return results
}

/**
 * Compare metadata to detect changes
 */
export function compareMetadata(oldMeta, newMeta) {
  const changes = []
  
  if (!oldMeta || !newMeta) return changes
  
  // Check version changes
  if (oldMeta.version !== newMeta.version) {
    changes.push({
      type: 'version',
      old: oldMeta.version,
      new: newMeta.version
    })
  }
  
  // Check skill count changes (SKILL.md, AGENTS.md, MANUAL.md)
  if (oldMeta.skillCount !== newMeta.skillCount) {
    changes.push({
      type: 'skillCount',
      old: oldMeta.skillCount,
      new: newMeta.skillCount
    })
  }
  
  // Check skills added/removed (SKILL.md)
  if (oldMeta.skills && newMeta.skills) {
    const oldSkills = new Set(oldMeta.skills)
    const newSkills = new Set(newMeta.skills)
    
    const added = [...newSkills].filter(s => !oldSkills.has(s))
    const removed = [...oldSkills].filter(s => !newSkills.has(s))
    
    if (added.length > 0 || removed.length > 0) {
      changes.push({
        type: 'skills',
        added,
        removed
      })
    }
  }
  
  // Check component changes (ARCHITECTURE.md)
  if (oldMeta.components && newMeta.components) {
    const oldComps = new Set(oldMeta.components)
    const newComps = new Set(newMeta.components)
    
    const added = [...newComps].filter(c => !oldComps.has(c))
    const removed = [...oldComps].filter(c => !newComps.has(c))
    
    if (added.length > 0 || removed.length > 0) {
      changes.push({
        type: 'components',
        added,
        removed
      })
    }
  }
  
  // Check ADR changes (ARCHITECTURE.md)
  if (oldMeta.adrs && newMeta.adrs) {
    const oldAdrs = new Set(oldMeta.adrs)
    const newAdrs = new Set(newMeta.adrs)
    
    const added = [...newAdrs].filter(a => !oldAdrs.has(a))
    
    if (added.length > 0) {
      changes.push({
        type: 'adrs',
        added
      })
    }
  }
  
  // Check requirement changes (PRD.md)
  if (oldMeta.requirements && newMeta.requirements) {
    const oldReqs = new Set(oldMeta.requirements)
    const newReqs = new Set(newMeta.requirements)
    
    const added = [...newReqs].filter(r => !oldReqs.has(r))
    const removed = [...oldReqs].filter(r => !newReqs.has(r))
    
    if (added.length > 0 || removed.length > 0) {
      changes.push({
        type: 'requirements',
        added,
        removed
      })
    }
  }
  
  return changes
}
