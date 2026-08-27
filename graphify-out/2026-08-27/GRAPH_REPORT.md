# Graph Report - SewaKost  (2026-08-25)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 5017 nodes · 11477 edges · 278 communities (245 shown, 33 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 129 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `e6457d19`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Illuminate\Http\RedirectResponse
- Kost
- live-browser.js
- User
- checks.mjs
- Illuminate\Database\Eloquent\Factories\Factory
- injected/index.mjs
- design-system.mjs
- setLiveState
- connectSSE
- Category
- detect-text.mjs
- TestCase
- live-server.mjs
- detect-antipatterns-browser.js
- svelte-component.mjs
- concept-seed.mjs
- hook-lib.mjs
- modern-screenshot.umd.js
- el
- detect-antipatterns.mjs
- css-cascade.mjs
- gray
- initPageChat
- manual-apply.mjs
- search
- parseAnyColor
- live-commit-manual-edits.mjs
- doc-sync-watcher.ts
- hook-before-edit.mjs
- context.mjs
- impeccable-config.mjs
- live-accept.mjs
- hook-admin.mjs
- live-wrap.mjs
- live-copy-edit-agent.mjs
- Illuminate\Database\Migrations\Migration
- slide_search_core.py
- initGlobalBar
- live-poll.mjs
- design-parser.mjs
- scanCssTextForPulsingDot
- checkElementGptBorderShadowDOM
- runHook
- KostDocumentRequirement
- InvalidKostTransitionException
- TestTailwindConfigGenerator
- design_system.py
- devDependencies
- impeccable-paths.mjs
- opencode.json
- OtpVerification
- html-token-validator.py
- event-validation.mjs
- roots.mjs
- OtpVerificationMail
- parseRgb
- insert-ui.mjs
- live-manual-edit-evidence.mjs
- padding-y
- BM25
- parseAnyColor
- handleManualEditActivity
- manual-edit-routes.mjs
- search
- accept-css.mjs
- live-inject.mjs
- svelte-ast.mjs
- checkQuality
- staleness.mjs
- spacing
- detect-url.mjs
- sveltekit-adapter.mjs
- tanstack-adapter.mjs
- TailwindConfigGenerator
- resolveLengthPx
- doctor.mjs
- session-store.mjs
- mountSvelteComponentVariant
- onAnnotDown
- captureElementToBlob
- generate-slide.py
- live.mjs
- collectBrowserFindings
- serve-question.mjs
- staleness-deep.mjs
- DesignSystemGenerator
- color
- resolveLiveInjectionAnchor
- test_design_system_mode.py
- fetch-background.py
- context-signals.mjs
- sampleCssBackground
- tag-strategy.mjs
- generate-image.mjs
- discoverTargetCandidates
- critique-storage.mjs
- createLiveBrowserSessionState
- BM25
- icon/generate.py
- fontSize
- md
- checkHeadingRhythmDOM
- createLiveBrowserDomHelpers
- detect-utils.mjs
- journal.mjs
- live-status.mjs
- TestShadcnInstaller
- _palette_is_dark
- composer.json
- scripts
- extract-colors.cjs
- validate-asset.cjs
- radius
- resolveProject
- StaticElement
- filterFindings
- frameworks/index.mjs
- design-tokens-starter.json
- surface-briefs.mjs
- browser-script-parts.mjs
- pin.mjs
- pattern-analyzer.mjs
- destructive
- validate-tokens.cjs
- card
- input
- generation-preflight.mjs
- ShadcnInstaller
- .check_shadcn_config
- .generate_config_string
- require-dev
- inject-brand-context.cjs
- embed-tokens.cjs
- detect-csp.mjs
- embed-prompt.mjs
- palette.mjs
- patch
- test_tailwind_config_gen.py
- ._base_config
- logo/generate.py
- generate-tokens.cjs
- button
- duration
- staleness-notice.mjs
- syncEditBadgeHitProxies
- AppServiceProvider
- _run
- sync-brand-to-tokens.cjs
- template-extensions.mjs
- Illuminate\Foundation\Http\FormRequest
- setup
- source-lock.mjs
- config
- checkHeadingRhythmDOM
- detect_domain
- _select_palette_for_mode
- psr-4
- logging.php
- $type
- radius
- lg
- profile/edit.blade.php
- require
- post-create-project-cmd
- .opencode/opencode.json
- xl
- none
- detect.mjs
- checkElementRadialSpotlightDOM
- hook.mjs
- provider.mjs
- validate_data.py
- ExampleTest
- extra
- .temp_project
- console.php
- graphify.js
- test_sync_brand_to_tokens.py
- main
- 16
- 1
- 3
- 8
- destructive-foreground
- muted
- primary-foreground
- ring
- secondary-foreground
- .__init__
- start-container
- layouts.navigation
- .test_add_components_no_config
- .test_list_installed_empty
- .test_list_installed_with_components
- .test_init_dry_run
- .test_check_shadcn_config_not_exists
- .test_get_installed_components_with_files
- .test_add_components_no_components
- .test_add_fonts
- .test_recommend_plugins
- .test_generate_typescript_config
- .test_generate_config_with_colors
- .test_generate_config_with_plugins
- .test_validate_config_no_content
- .test_validate_config_empty_theme
- .test_write_config
- .test_init_javascript
- .test_write_config_creates_content
- .test_write_config_invalid_path
- .test_full_configuration_javascript
- .test_default_output_path_typescript
- .test_base_config_structure
- .test_default_content_paths_vue
- .test_add_colors

## God Nodes (most connected - your core abstractions)
1. `User` - 330 edges
2. `Kost` - 288 edges
3. `TestCase` - 66 edges
4. `TailwindConfigGenerator` - 58 edges
5. `Category` - 53 edges
6. `parseAnyColor()` - 45 edges
7. `runHook()` - 40 edges
8. `parseAnyColor()` - 40 edges
9. `KostPolicyTest` - 38 edges
10. `collectBrowserFindings()` - 37 edges

## Surprising Connections (you probably didn't know these)
- `KostAddressTest` --references--> `Kost`  [EXTRACTED]
  tests/Feature/Admin/KostAddressTest.php → app/Domain/Kost/Models/Kost.php
- `KostDocumentRequirementTest` --references--> `Kost`  [EXTRACTED]
  tests/Feature/Admin/KostDocumentRequirementTest.php → app/Domain/Kost/Models/Kost.php
- `KostFacilitiesRulesTest` --references--> `Kost`  [EXTRACTED]
  tests/Feature/Admin/KostFacilitiesRulesTest.php → app/Domain/Kost/Models/Kost.php
- `KostSubmissionWorkflowTest` --references--> `User`  [EXTRACTED]
  tests/Feature/SuperAdmin/KostSubmissionWorkflowTest.php → app/Domain/Identity/Models/User.php
- `KostFacilitiesRulesTest` --references--> `User`  [EXTRACTED]
  tests/Feature/Admin/KostFacilitiesRulesTest.php → app/Domain/Identity/Models/User.php

## Import Cycles
- None detected.

## Communities (278 total, 33 thin omitted)

### Community 0 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.04
Nodes (37): DocumentRequirementController, KostController, KostImageController, AuthenticatedSessionController, ConfirmablePasswordController, EmailVerificationController, NewPasswordController, PasswordController (+29 more)

### Community 1 - "Kost"
Cohesion: 0.03
Nodes (13): Kost, KostImage, KostImagePolicy, KostImageTest, KostPaymentTest, KostBasicTest, KostCancelWorkflowTest, KostStatusProtectionTest (+5 more)

### Community 2 - "live-browser.js"
Cohesion: 0.03
Nodes (126): addManualContextText(), applyEditing(), applyGlobalBarLabelState(), applyPlaceholderSizingStyles(), bufferToBase64(), buildCollapsible(), buildColorModels(), buildListHtml() (+118 more)

### Community 3 - "User"
Cohesion: 0.03
Nodes (11): User, UserPolicy, KostPolicy, Illuminate\Foundation\Auth\User, Illuminate\Support\Facades\Gate, Illuminate\Support\Facades\Route, AuthenticationTest, RbacTest (+3 more)

### Community 4 - "checks.mjs"
Cohesion: 0.03
Nodes (145): ANIMATION_VALUE_KEYWORDS, borderColorsFromStyle(), borderWidthsFromStyle(), buildHtmlPatternCorpora(), checkClippedOverflow(), checkColors(), checkEdgeFlushCardsDOM(), checkElementAIPaletteDOM() (+137 more)

### Community 5 - "Illuminate\Database\Eloquent\Factories\Factory"
Cohesion: 0.04
Nodes (27): AddressFactory, CategoryFactory, KostDocumentRequirementFactory, static, KostFactory, static, KostImageFactory, static (+19 more)

### Community 6 - "injected/index.mjs"
Cohesion: 0.06
Nodes (69): addBrowserFindings(), addVisualContrastFindings(), addVisualContrastResult(), analyzeVisualContrast(), analyzeVisualContrastCandidate(), blendRgba(), browserColorsClose(), browserDesignSystemConfig() (+61 more)

### Community 7 - "design-system.mjs"
Cohesion: 0.06
Nodes (72): addClampEndpoints(), addColorObject(), addDesignColor(), addFontSizeStep(), addRoundedScale(), addRoundedToken(), addSidecarColors(), addSidecarRadii() (+64 more)

### Community 8 - "setLiveState"
Cohesion: 0.08
Nodes (69): abandonForeignSession(), buildInsertPlaceholderSnapshotFromDom(), buildPickedAnchorSnapshot(), cancelEditing(), cancelEditingToPicking(), cancelInsertConfigure(), captureAndEmit(), checkpointPayload() (+61 more)

### Community 9 - "connectSSE"
Cohesion: 0.07
Nodes (71): abortSvelteComponentInjection(), applyParamDefaults(), applyParamValue(), applySavedSessionMeta(), clampVariantIndex(), clearSession(), closedClipPath(), completeParameterPublication() (+63 more)

### Community 10 - "Category"
Cohesion: 0.04
Nodes (15): Address, Category, RoomType, CategoryPolicy, CategoryController, StoreCategoryRequest, UpdateCategoryRequest, CategorySeeder (+7 more)

### Community 11 - "detect-text.mjs"
Cohesion: 0.05
Nodes (57): blankCssComments(), BLOCK_BRACE_PREFIX_KEYWORDS, CSS_IN_JS_EXTENSIONS, detectText(), extFromFilePath(), extractCSSinJS(), extractStyleBlocks(), findCSSinJSTemplates() (+49 more)

### Community 12 - "TestCase"
Cohesion: 0.03
Nodes (19): App\Http\Middleware\VerifyCsrfToken, Illuminate\Foundation\Http\Middleware\VerifyCsrfToken, Illuminate\Foundation\Testing\LazilyRefreshDatabase, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Http\UploadedFile, Illuminate\Support\Facades\Hash, Illuminate\Support\Facades\Storage (+11 more)

### Community 13 - "live-server.mjs"
Cohesion: 0.07
Nodes (60): eventPriority(), selectAvailablePendingEvent(), acknowledgePendingEvent(), activeSessionSummaries(), agentPollingConnected(), annotRoot, args, broadcast() (+52 more)

### Community 14 - "detect-antipatterns-browser.js"
Cohesion: 0.05
Nodes (71): addBrowserFindings(), addVisualContrastFindings(), addVisualContrastResult(), analyzeVisualContrast(), browserColorsClose(), browserDesignSystemConfig(), browserHasDirectText(), browserPrimaryFont() (+63 more)

### Community 15 - "svelte-component.mjs"
Cohesion: 0.07
Nodes (57): collectUnusedSelectors(), verifyAcceptedSource(), applyLegacyDeferredAcceptsOnStartup(), buildPropsScriptV2(), loadSvelteCompiler(), appendCssToSvelteStyle(), appendSanitizedCssRule(), applyDeferredSvelteComponentAccepts() (+49 more)

### Community 16 - "concept-seed.mjs"
Cohesion: 0.07
Nodes (53): API_BASE, API_TIMEOUT_MS, apiBudgetMs(), dealCompositions(), driveSelection(), fetchRoll(), here, loadLocal() (+45 more)

### Community 17 - "hook-lib.mjs"
Cohesion: 0.06
Nodes (55): ACK_EXTS, ADVISORY_RULES, applyConfigSource(), applyDetectorConfigSource(), applyPatchText(), canonicalPathCache, clampByte(), cloneDefaultConfig() (+47 more)

### Community 18 - "modern-screenshot.umd.js"
Cohesion: 0.09
Nodes (55): ae(), be(), bt(), Ce(), s(), Ct(), de(), dt() (+47 more)

### Community 19 - "el"
Cohesion: 0.07
Nodes (55): actionLabel(), applyConfigureBarChrome(), bindConfigureCountPillTooltip(), bindConfigureInlineControlHover(), bindConfigureModifierPillHover(), buildConfigureActionControl(), buildConfigureCountControl(), buildConfigureRow() (+47 more)

### Community 20 - "detect-antipatterns.mjs"
Cohesion: 0.08
Nodes (45): confirm(), detectCli(), detectLocalFile(), dim(), fileUrlToLocalPath(), formatAdvisorySection(), formatFindings(), formatFindingsBody() (+37 more)

### Community 21 - "css-cascade.mjs"
Cohesion: 0.07
Nodes (37): applyStaticDeclaration(), buildBorderOverrideMap(), parseShorthand(), resolveVar(), buildStaticStyleMap(), buildStaticWindow(), collectStaticCssRules(), collectStaticCssText() (+29 more)

### Community 22 - "gray"
Cohesion: 0.05
Nodes (53): $type, $value, $type, $value, $type, $value, $type, $value (+45 more)

### Community 23 - "initPageChat"
Cohesion: 0.08
Nodes (53): armPageChatForTyping(), attachSteerFocusDebug(), attachSteerFocusGuard(), buildSteerProcessingDots(), buildSteerQueueHint(), clearSteerAwaitTimer(), clearSteerFocusRecoverTimer(), collapsePageChat() (+45 more)

### Community 24 - "manual-apply.mjs"
Cohesion: 0.09
Nodes (49): addOpToManualApplyChunk(), APPLY_EVENT_HARD_TIMEOUT_MS, APPLY_EVENT_SOFT_DEADLINE_MS, buildManualApplyAgentAction(), clearManualApplyTransaction(), collectManualApplyFiles(), compactManualApplyBatch(), compactManualApplyCandidates() (+41 more)

### Community 25 - "search"
Cohesion: 0.07
Nodes (42): BM25, detect_domain(), get_cip_brief(), _load_csv(), Load CSV and return list of dicts, Core search function using BM25, Auto-detect the most relevant domain from query, Main search function with auto-domain detection (+34 more)

### Community 26 - "parseAnyColor"
Cohesion: 0.09
Nodes (48): checkBorders(), checkCreamPalette(), checkElementBorders(), checkElementBordersDOM(), checkElementColors(), checkElementColorsDOM(), checkElementGlowDOM(), checkElementHoverContrast() (+40 more)

### Community 27 - "live-commit-manual-edits.mjs"
Cohesion: 0.10
Nodes (49): allEntryIds(), argVal(), buildRepairBatch(), candidatesForEntry(), changedFilesSinceSnapshot(), clearAppliedEntries(), collectApplyOwnedFiles(), collectRollbackFiles() (+41 more)

### Community 28 - "doc-sync-watcher.ts"
Cohesion: 0.08
Nodes (40): detectChanges(), DocSyncWatcher(), hashFile(), loadFileHashes(), WATCHED_FILES, analyzePatterns(), detectCrossSessionPatterns(), detectFileEditPatterns() (+32 more)

### Community 29 - "hook-before-edit.mjs"
Cohesion: 0.08
Nodes (47): allow(), bumpCursorDenial(), cursorBlockMessage(), deny(), detectProposedHtml(), done(), escapeRegExp(), findingSignature() (+39 more)

### Community 30 - "context.mjs"
Cohesion: 0.06
Nodes (57): appendAutonomyCounterDirective(), appendBuildPathDirective(), appendDetectorFallback(), appendImageGenDirective(), appendImageToolsDirective(), appendSubagentAuthorizationDirective(), appendSurfaceBriefContext(), automaticHookMode() (+49 more)

### Community 31 - "impeccable-config.mjs"
Cohesion: 0.10
Nodes (45): applyDetectionConfigSource(), clampByte(), cleanIgnoreValueDisplay(), cloneDetectionConfig(), cloneRawDetectionConfig(), COLOR_CHANNEL_FORMATS, colorIgnoreKey(), DEFAULT_DETECTION_CONFIG (+37 more)

### Community 32 - "live-accept.mjs"
Cohesion: 0.09
Nodes (45): IMPECCABLE_DIR, matchesTemplateExtension(), acceptCli(), acceptReceiptPath(), argVal(), buildAcceptedWrappedSource(), buildCarbonizeReplacement(), decodeHtmlAttr() (+37 more)

### Community 33 - "hook-admin.mjs"
Cohesion: 0.12
Nodes (42): ACTIONS, addIgnoreFile(), addIgnoreRule(), addIgnoreValue(), DETECTOR_CONFIG_KEYS, detectorSection(), fileHasImpeccableHookMarker(), HOOK_MANIFEST_TARGETS (+34 more)

### Community 34 - "live-wrap.mjs"
Cohesion: 0.12
Nodes (39): hasGeneratedHeader(), HEADER_MARKERS, isGeneratedFile(), isGitIgnored(), resolveSourceTraits(), argVal(), buildInsertWrapperLines(), computeInsertLine() (+31 more)

### Community 35 - "live-copy-edit-agent.mjs"
Cohesion: 0.12
Nodes (42): applyMockWrites(), buildCopyEditBatchPrompt(), checkFrameworkSourceSyntax(), chooseCopyEditAgent(), COMMAND_AUTH_CACHE, commandAuthed(), commandExists(), compactBatchCandidates() (+34 more)

### Community 36 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.08
Nodes (3): Illuminate\Database\Migrations\Migration, Illuminate\Database\Schema\Blueprint, Illuminate\Support\Facades\Schema

### Community 37 - "slide_search_core.py"
Cohesion: 0.08
Nodes (36): format_context(), format_result(), main(), Format a single search result for display, Format contextual recommendations for display., BM25, calculate_pattern_break(), detect_domain() (+28 more)

### Community 38 - "initGlobalBar"
Cohesion: 0.09
Nodes (39): agentHasWorkInFlight(), agentStatusText(), barPaletteForTheme(), brandMarkSvg(), buildDesignHeader(), buildParamsPanel(), designPanelCss(), detectPageTheme() (+31 more)

### Community 39 - "live-poll.mjs"
Cohesion: 0.10
Nodes (38): completionAckForAcceptResult(), completionTypeForAcceptResult(), PREVIEW_MODES_WITHOUT_SOURCE_MARKERS, acceptInstructions(), bootInstructions(), deferredWrapperInstructions(), generateInstructions(), insertScaffoldInstructions() (+30 more)

### Community 40 - "design-parser.mjs"
Cohesion: 0.13
Nodes (39): assessCoverage(), buildColor(), CANONICAL_SECTIONS, collectBullets(), collectColorValues(), collectParagraphs(), detectFormat(), extractColors() (+31 more)

### Community 41 - "scanCssTextForPulsingDot"
Cohesion: 0.10
Nodes (37): buildHtmlPatternCorpora(), checkColors(), checkElementAIPaletteDOM(), checkElementGlow(), checkGlow(), checkHtmlPatterns(), checkRadialSpotlight(), collectCssCustomProps() (+29 more)

### Community 42 - "checkElementGptBorderShadowDOM"
Cohesion: 0.38
Nodes (7): borderColorsFromStyle(), borderWidthsFromStyle(), checkElementGptBorderShadow(), checkElementGptBorderShadowDOM(), checkGptThinBorderWideShadow(), shadowLayerAlpha(), shadowMaxBlurPx()

### Community 43 - "runHook"
Cohesion: 0.12
Nodes (35): appendDesignSystemNote(), appendDesignSystemNoteOnce(), bumpEditCount(), clampGroupedToBudget(), clampLastLine(), clampToBudget(), commitFooterShown(), consumeSessionNoticeFlag() (+27 more)

### Community 44 - "KostDocumentRequirement"
Cohesion: 0.09
Nodes (3): KostDocumentRequirement, KostDocumentRequirementPolicy, KostDocumentRequirementTest

### Community 45 - "InvalidKostTransitionException"
Cohesion: 0.07
Nodes (13): ApproveKost, CancelKostSubmission, PublishKost, RejectKost, SubmitKostForReview, InvalidKostSubmissionException, self, InvalidKostTransitionException (+5 more)

### Community 46 - "TestTailwindConfigGenerator"
Cohesion: 0.06
Nodes (16): Test adding colors multiple times., Test adding full color palette., Test adding custom breakpoints., Test TailwindConfigGenerator class., Test that adding same plugin twice doesn't duplicate., Test plugin recommendations for Next.js., Test initialization with default settings., Test generating JavaScript configuration. (+8 more)

### Community 47 - "design_system.py"
Cohesion: 0.09
Nodes (27): ansi_ljust(), _detect_page_type(), format_ascii_box(), format_markdown(), format_master_md(), format_page_override_md(), generate_design_system(), _generate_intelligent_overrides() (+19 more)

### Community 48 - "devDependencies"
Cohesion: 0.07
Nodes (29): alpinejs, autoprefixer, concurrently, laravel-vite-plugin, leaflet, dependencies, alpinejs, leaflet (+21 more)

### Community 49 - "impeccable-paths.mjs"
Cohesion: 0.16
Nodes (21): CRITIQUE_DIR, firstExisting(), getDesignSidecarCandidates(), getDesignSidecarPath(), getImpeccableDir(), getLegacyLiveConfigPath(), getLegacyLiveServerPath(), getLiveAnnotationsDir() (+13 more)

### Community 50 - "opencode.json"
Cohesion: 0.07
Nodes (28): models, npm, options, command, enabled, type, lsp, mcp (+20 more)

### Community 51 - "OtpVerification"
Cohesion: 0.07
Nodes (6): OtpVerification, OtpService, Illuminate\Support\Collection, Illuminate\Support\Facades\Cache, OtpVerificationTest, OtpServiceTest

### Community 52 - "html-token-validator.py"
Cohesion: 0.13
Nodes (24): get_context(), is_allowed_exception(), is_allowed_rgba(), is_inside_block(), load_css_variables(), main(), print_result(), print_summary() (+16 more)

### Community 53 - "event-validation.mjs"
Cohesion: 0.13
Nodes (24): AGENT_PHASE_SET, FORBIDDEN_MANUAL_EDIT_TEXT_CHARS, INSERT_POSITIONS, isValidId(), isValidMountVariant(), isValidVariantId(), MOUNT_ERROR_MAX_LENGTH, MOUNT_URL_MAX_LENGTH (+16 more)

### Community 54 - "roots.mjs"
Cohesion: 0.15
Nodes (27): CANDIDATE_SCAN_IGNORED, consumeTargetArg(), CONTEXT_FALLBACK_DIRS, DESIGN_NAMES, DEV_CONFIG_MARKERS, discoverAppCandidates(), enterLiveRoot(), exists() (+19 more)

### Community 55 - "OtpVerificationMail"
Cohesion: 0.17
Nodes (10): OtpVerificationMail, KostApprovedMail, KostRejectedMail, KostSubmittedMail, Illuminate\Bus\Queueable, Illuminate\Mail\Mailable, Illuminate\Mail\Mailables\Attachment, Illuminate\Mail\Mailables\Content (+2 more)

### Community 56 - "parseRgb"
Cohesion: 0.13
Nodes (30): checkCreamPalette(), checkElementColors(), checkElementColorsDOM(), checkElementGlowDOM(), checkElementHoverContrast(), checkElementIconTile(), checkElementIconTileDOM(), checkHoverContrast() (+22 more)

### Community 57 - "insert-ui.mjs"
Cohesion: 0.09
Nodes (13): canCreateInsert(), clampPlaceholderSize(), computeInsertPosition(), groupSiblingRows(), hitSiblingInsertGap(), horizontalOverlap(), insertCreateDisabledReason(), insertLineCoords() (+5 more)

### Community 58 - "live-manual-edit-evidence.mjs"
Cohesion: 0.15
Nodes (26): analyzeSourceHint(), buildCandidatesForOp(), buildContextHintsByRef(), buildManualEditEvidence(), collectSearchFiles(), countOps(), decodeBasicHtml(), escapeRegExp() (+18 more)

### Community 59 - "padding-y"
Cohesion: 0.67
Nodes (4): padding-y, padding-y, $type, $value

### Community 60 - "BM25"
Cohesion: 0.11
Nodes (19): BM25, detect_domain(), _load_csv(), Load CSV and return list of dicts, Core search function using BM25, Auto-detect the most relevant domain from query, Main search function with auto-domain detection, Search across all domains and combine results (+11 more)

### Community 61 - "parseAnyColor"
Cohesion: 0.13
Nodes (22): checkTextOcclusionDOM(), clamp01(), colorFunctionToRgb(), decodeSrgbChannel(), elementDirectText(), encodeSrgbChannel(), hslToRgb(), hwbToRgb() (+14 more)

### Community 62 - "handleManualEditActivity"
Cohesion: 0.18
Nodes (25): clearStoredManualApplyState(), fetchPendingCount(), handleManualEditActivity(), hidePendingApplyDock(), manualApplyLoadingText(), manualApplyStateKey(), manualEditEventForCurrentPage(), numberOrNull() (+17 more)

### Community 63 - "manual-edit-routes.mjs"
Cohesion: 0.18
Nodes (21): args, buffer, cwd, pageUrlFilter, remaining, compactManualLogText(), summarizeManualApplyFailures(), summarizeManualDiagnostics() (+13 more)

### Community 64 - "search"
Cohesion: 0.12
Nodes (18): _domain_keywords(), _get_bm25(), _load_csv(), _load_product_keywords(), Load CSV and return list of dicts, with mtime-based caching., Fitted BM25 index for this file+columns, with mtime-based caching., Core search function using BM25. Returns (results, bm25_or_none)., Nearest known vocabulary terms for a query that returned 0 hits, so the caller… (+10 more)

### Community 65 - "accept-css.mjs"
Cohesion: 0.20
Nodes (23): bakeParamValues(), collectAllSelectors(), collectSelectorsFromNodes(), escapeRegExp(), formatBody(), isToggleOn(), normalizeSelector(), normalizeToggleForVar() (+15 more)

### Community 66 - "live-inject.mjs"
Cohesion: 0.15
Nodes (21): describeInjectArtifacts(), frameworkIgnorePatterns(), resolveFramework(), applyNuxtLiveAdapter(), buildNuxtPlugin(), detectNuxtProject(), NUXT_PLUGIN_MARKER, NUXT_PLUGIN_NAME (+13 more)

### Community 67 - "svelte-ast.mjs"
Cohesion: 0.21
Nodes (20): Analysis, analyzeAttributes(), analyzeFragment(), analyzeNode(), analyzeSvelteMarkup(), applyReplacements(), classifyEachKey(), classifyRoots() (+12 more)

### Community 68 - "checkQuality"
Cohesion: 0.14
Nodes (16): checkElementOversizedH1(), checkElementOversizedH1DOM(), checkElementQuality(), checkElementQualityDOM(), checkOversizedH1(), checkQuality(), colorsNearlyMatch(), cssColorAlpha() (+8 more)

### Community 69 - "staleness.mjs"
Cohesion: 0.20
Nodes (21): collect(), readSidecarSchemaVersion(), BUILD_PATH_VALUES, checkBuildPathUnset(), checkConfig(), checkDesignSidecar(), checkNativePlatformEvidence(), checkProduct() (+13 more)

### Community 70 - "spacing"
Cohesion: 0.09
Nodes (22): $type, $value, $type, $value, $type, $value, $type, $value (+14 more)

### Community 71 - "detect-url.mjs"
Cohesion: 0.20
Nodes (18): createBrowserDetector(), detectUrl(), launchBrowser(), measureContentHiddenAfterReveal(), runVisualContrastFallback(), serializeDesignSystemForBrowser(), captureVisualContrastCandidate(), compareScreenshotContrast() (+10 more)

### Community 72 - "sveltekit-adapter.mjs"
Cohesion: 0.18
Nodes (20): applySvelteKitLiveAdapter(), buildSvelteLiveRootComponent(), defaultSvelteLayout(), detectSvelteKitProject(), ensureSvelteLiveRootComponent(), escapeRegExp(), fileIncludes(), findSvelteKitAppHtml() (+12 more)

### Community 73 - "tanstack-adapter.mjs"
Cohesion: 0.16
Nodes (20): tanstackStart, applyTanStackLiveAdapter(), buildTanStackLiveRootComponent(), detectTanStackStartProject(), escapeRegExp(), findRootRouteFile(), insertAfterLastImport(), isManagedComponent() (+12 more)

### Community 74 - "TailwindConfigGenerator"
Cohesion: 0.10
Nodes (12): main(), Add custom font families. Args: fonts: Dict of font_type: [font_names] e.g.,…, Add custom spacing values. Args: spacing: Dict of name: value e.g., {'18':…, Add custom breakpoints. Args: breakpoints: Dict of name: width e.g., {'3xl':…, Add plugin requirements. Args: plugins: List of plugin names e.g.,…, Get plugin recommendations based on configuration. Returns: List of recommended…, Generate Tailwind CSS configuration files., Validate configuration. Returns: Tuple of (valid, message) (+4 more)

### Community 75 - "resolveLengthPx"
Cohesion: 0.10
Nodes (27): checkElementHeroEyebrow(), checkElementHeroEyebrowDOM(), checkHeroEyebrow(), checkKickerAboveHeading(), checkKickerAboveHeadingDOM(), checkKickerAboveHeadingFromDoc(), checkNumberedSectionLabels(), checkNumberedSectionLabelsDOM() (+19 more)

### Community 76 - "doctor.mjs"
Cohesion: 0.15
Nodes (18): applyFixes(), cli(), parseArgs(), readProjectRootPatterns(), rel(), renderText(), safeRead(), SCRIPTS_DIR (+10 more)

### Community 77 - "session-store.mjs"
Cohesion: 0.12
Nodes (27): getLegacyLiveSessionsDir(), safeSessionId(), FORBIDDEN, verifyAcceptedFile(), completeCli(), completeThroughServer(), parseArgs(), readServerInfo() (+19 more)

### Community 78 - "mountSvelteComponentVariant"
Cohesion: 0.14
Nodes (21): acceptedDomAlreadyClean(), applyOriginalAttrsToSvelteAnchor(), commitAcceptedSvelteComponentToDom(), componentModuleCandidates(), describeMountFailure(), detectDevServerBase(), ensureAcceptedDomClean(), findAcceptedRuntimeWrappers() (+13 more)

### Community 79 - "onAnnotDown"
Cohesion: 0.15
Nodes (21): applyPlaceholderDimensions(), beginEditPin(), buildAnnotationsForCapture(), buildPinElement(), cancelEditingPin(), clampPlaceholderSize(), finalizeEditingPin(), initAnnotOverlay() (+13 more)

### Community 80 - "captureElementToBlob"
Cohesion: 0.12
Nodes (20): averageRgb01(), captureChromeNodes(), captureElementFromRenderedAncestor(), captureElementToBlob(), compileShader(), cssColorToRgb01(), dominantRgb01(), findBackdropAncestor() (+12 more)

### Community 81 - "generate-slide.py"
Cohesion: 0.15
Nodes (19): _e(), generate_chart_slide(), generate_cta_slide(), generate_deck(), generate_metrics_slide(), generate_problem_slide(), generate_solution_slide(), generate_testimonial_slide() (+11 more)

### Community 82 - "live.mjs"
Cohesion: 0.17
Nodes (17): parseCliOptions(), resolveProjectRoot(), resolveTargetSelection(), getLegacyLiveAnnotationsDir(), parseTargetOptions(), parseTargetPath(), TargetArgError, __dirname (+9 more)

### Community 83 - "collectBrowserFindings"
Cohesion: 0.16
Nodes (20): browserFindingsFromMap(), checkBorders(), checkEdgeFlushCardsDOM(), checkElementBlinkingCursorDOM(), checkElementBorders(), checkElementBordersDOM(), checkElementPseudoStripeDOM(), checkElementTextOverflowDOM() (+12 more)

### Community 84 - "serve-question.mjs"
Cohesion: 0.15
Nodes (16): browserOpenCommand(), openSystemBrowser(), answerFile(), esc(), flipFile(), loadRound(), localImages, nextFile() (+8 more)

### Community 85 - "staleness-deep.mjs"
Cohesion: 0.18
Nodes (19): checkDesignCoverage(), checkDesignDrift(), checkDetectorIgnores(), checkHookInstallation(), checkLegacyLiveState(), checkWorkspaces(), collectHookCommands(), finding() (+11 more)

### Community 86 - "DesignSystemGenerator"
Cohesion: 0.14
Nodes (10): DesignSystemGenerator, Generates design system recommendations from aggregated searches., Load reasoning rules from CSV., Execute searches across multiple domains., Find matching reasoning rule for a category., Apply reasoning rules to search results., Select best matching result based on priority keywords., Extract results list from search result dict. (+2 more)

### Community 87 - "color"
Cohesion: 0.11
Nodes (19): $type, $value, background, foreground, muted-foreground, primary, primary-hover, secondary (+11 more)

### Community 88 - "resolveLiveInjectionAnchor"
Cohesion: 0.16
Nodes (19): buildSvelteExpressionTextMap(), buildSveltePropValuesFromLiveElement(), buildSveltePropValuesV2(), cloneWithoutElements(), collectTextNodes(), collectVisibleTexts(), cssEscapeIdent(), elementMatchesOriginalMarkup() (+11 more)

### Community 89 - "test_design_system_mode.py"
Cohesion: 0.16
Nodes (10): _filter_anti_patterns_for_mode(), _query_wants_dark(), True when a styles.csv row describes itself as dark-first., True when the query explicitly asks for a dark theme., Resolve the mode the rest of the output has to agree with., Drop "avoid dark mode" advice once dark mode is the resolved answer., _resolve_color_mode(), _style_is_dark_primary() (+2 more)

### Community 90 - "fetch-background.py"
Cohesion: 0.17
Nodes (17): generate_css_for_background(), get_background_image(), get_curated_images(), get_overlay_css(), get_pexels_search_url(), load_backgrounds_config(), load_brand_colors(), main() (+9 more)

### Community 91 - "context-signals.mjs"
Cohesion: 0.19
Nodes (17): extractPlatform(), hasVisualImplementation(), loadContext(), cli(), COMMON_DEV_PORTS, devServerSignals(), gatherSignals(), gitSignals() (+9 more)

### Community 92 - "sampleCssBackground"
Cohesion: 0.16
Nodes (18): analyzeVisualContrastCandidate(), blendRgba(), clampByte(), firstCssUrl(), getLayerValue(), loadVisualContrastImage(), parseObjectPosition(), parsePositionPair() (+10 more)

### Community 93 - "tag-strategy.mjs"
Cohesion: 0.20
Nodes (17): appendOriginToDirective(), buildTagBlock(), commentClose(), commentOpen(), detectLineEnding(), findCspMetaTags(), getAttr(), insertTag() (+9 more)

### Community 94 - "generate-image.mjs"
Cohesion: 0.17
Nodes (13): crc32(), hash32(), hslToRgb(), out, palette(), pngChunk(), pngFake(), promptFile (+5 more)

### Community 95 - "discoverTargetCandidates"
Cohesion: 0.17
Nodes (18): directChildDirs(), discoverRootsForPattern(), discoverTargetCandidates(), escapeRegExp(), expandSimplePattern(), extractSectionValue(), findTargetExample(), isCandidateProjectRoot() (+10 more)

### Community 96 - "critique-storage.mjs"
Cohesion: 0.32
Nodes (11): coerceSlug(), listSnapshots(), main(), nowFilenameStamp(), parseFrontmatter(), readLatestSnapshot(), readLatestSnapshotMatching(), readTrend() (+3 more)

### Community 97 - "createLiveBrowserSessionState"
Cohesion: 0.20
Nodes (14): createLiveBrowserSessionState(), clearHandled(), clearScrollY(), clearSession(), isHandled(), loadSession(), markHandled(), nextCheckpointRevision() (+6 more)

### Community 98 - "BM25"
Cohesion: 0.15
Nodes (9): BM25, _normalize(), Apply synonym substitution before tokenizing., BM25 ranking algorithm for text search, Lowercase, normalize synonyms, split, remove punctuation, filter stopwords, Build BM25 index from documents, Score all documents against query, All indexed terms, for suggestion/typo-recovery purposes. (+1 more)

### Community 99 - "icon/generate.py"
Cohesion: 0.20
Nodes (15): apply_color(), apply_viewbox_size(), extract_svgs(), generate_batch(), generate_icon(), generate_sizes(), load_env(), main() (+7 more)

### Community 100 - "fontSize"
Cohesion: 0.12
Nodes (16): $type, $value, $type, $value, $type, $value, $type, $value (+8 more)

### Community 101 - "md"
Cohesion: 0.67
Nodes (4): $type, $value, md, md

### Community 102 - "checkHeadingRhythmDOM"
Cohesion: 0.18
Nodes (16): checkHeadingRhythmDOM(), clusterTop(), edgeAbove(), edgeBelow(), hasOwnTopBoundary(), insideSmallCard(), isVisibleFlow(), overlapsX() (+8 more)

### Community 103 - "createLiveBrowserDomHelpers"
Cohesion: 0.19
Nodes (10): createLiveBrowserDomHelpers(), cssId(), liveUiRoot(), makeFrozenAnchor(), own(), pickable(), rectIsUsableAnchor(), uiAppend() (+2 more)

### Community 104 - "detect-utils.mjs"
Cohesion: 0.33
Nodes (11): astro, detectAstroProject(), fileExists(), findConfigFile(), firstExistingFile(), hasAnyDependency(), literalConfigFiles(), readPackageDeps() (+3 more)

### Community 105 - "journal.mjs"
Cohesion: 0.26
Nodes (14): PATCH_UNDOERS, clearInjectJournal(), healArtifact(), healInjectJournal(), INJECT_JOURNAL_RELPATH, INJECT_JOURNAL_VERSION, injectJournalPath(), insideProject() (+6 more)

### Community 106 - "live-status.mjs"
Cohesion: 0.30
Nodes (13): collectManualApplyFiles(), manualApplyReplyCommand(), manualApplyResumeHint(), mountFailureAction(), parseArgs(), renderSummary(), resumeCli(), summarizeManualApplyEvent() (+5 more)

### Community 107 - "TestShadcnInstaller"
Cohesion: 0.14
Nodes (8): Test adding components that are already installed., Test adding components in dry run mode., Test ShadcnInstaller class., Test listing installed components without config., Test initialization with custom project root., Test checking for existing shadcn config., Test getting installed components when none exist., TestShadcnInstaller

### Community 108 - "_palette_is_dark"
Cohesion: 0.18
Nodes (7): _palette_is_dark(), WCAG relative luminance of a #RRGGBB string, or None if unparseable., True when a colors.csv row's Background is a dark surface., _relative_luminance(), The exact reproduction from issue #428., TestEndToEndCoherence, TestLuminance

### Community 109 - "composer.json"
Cohesion: 0.14
Nodes (13): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+5 more)

### Community 110 - "scripts"
Cohesion: 0.14
Nodes (14): scripts, dev, post-autoload-dump, post-update-cmd, pre-package-uninstall, test, Composer\\Config::disableProcessTimeout, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump (+6 more)

### Community 111 - "extract-colors.cjs"
Cohesion: 0.22
Nodes (11): calculateCompliance(), colorDistance(), displayPalette(), extractHexColors(), findNearestBrandColor(), fs, generateImageMagickCommand(), hexToRgb() (+3 more)

### Community 112 - "validate-asset.cjs"
Cohesion: 0.25
Nodes (13): checkManifest(), formatBytes(), formatOutput(), fs, main(), parseFilename(), path, RULES (+5 more)

### Community 113 - "radius"
Cohesion: 0.18
Nodes (15): $type, $value, sm, $type, $value, primitive, radius, shadow (+7 more)

### Community 114 - "resolveProject"
Cohesion: 0.18
Nodes (15): contextSourcePath(), contextSourceStatus(), firstExisting(), isPathInside(), isPathInsideOrEqual(), nearestProjectLikeRoot(), nearestTargetContextRoot(), resolveCandidateContextSummary() (+7 more)

### Community 116 - "filterFindings"
Cohesion: 0.23
Nodes (14): cleanIgnoreValueDisplay(), extractFindingIgnoreValue(), extractFindingIgnoreValueRaw(), extractMotionIgnoreValue(), filterFindings(), formatFindingIgnoreHint(), formatFindingLine(), isAdvisoryFinding() (+6 more)

### Community 117 - "frameworks/index.mjs"
Cohesion: 0.15
Nodes (12): COMMENT_SYNTAXES, FRAMEWORKS, INJECT_KINDS, PREVIEW_MODES, SOURCE_TRAIT_DEFAULTS, STYLE_MODES, TAG_PATCH_KIND, nextjs (+4 more)

### Community 118 - "design-tokens-starter.json"
Cohesion: 0.15
Nodes (12): component, $type, $value, dark, semantic, $schema, $type, $value (+4 more)

### Community 119 - "surface-briefs.mjs"
Cohesion: 0.31
Nodes (12): getSurfaceBriefDir(), listSurfaceBriefs(), normalizeSurfaceTarget(), parseSurfaceBrief(), resolveSurfaceBrief(), SURFACE_BRIEF_VERSION, surfaceBriefPathForTarget(), writeSurfaceBrief() (+4 more)

### Community 120 - "browser-script-parts.mjs"
Cohesion: 0.19
Nodes (10): assembleLiveBrowserScript(), assertLiveBrowserScriptParts(), LIVE_BROWSER_SCRIPT_PARTS, readLiveBrowserScriptParts(), resolveLiveBrowserScriptParts(), loadBrowserScripts(), LIVE_CHROME_MOUNT_CONTRACT, LIVE_UI_COMPONENT_IDS (+2 more)

### Community 121 - "pin.mjs"
Cohesion: 0.22
Nodes (11): CODEX_HARNESSES, commandPrefixForSkillsDir(), __dirname, findHarnessDirs(), generatePinnedSkill(), HARNESS_DIRS, loadCommandMetadata(), pin() (+3 more)

### Community 122 - "pattern-analyzer.mjs"
Cohesion: 0.23
Nodes (6): buildGrounding(), formatPattern(), generateDescription(), generateSkillBody(), generateSkillDraft(), generateSkillName()

### Community 123 - "destructive"
Cohesion: 0.67
Nodes (3): destructive, $type, $value

### Community 124 - "validate-tokens.cjs"
Cohesion: 0.24
Nodes (11): extensions, formatReport(), fs, getFiles(), main(), parseArgs(), path, patterns (+3 more)

### Community 125 - "card"
Cohesion: 0.20
Nodes (12): $type, $value, bg, bg, padding, shadow, card, bg (+4 more)

### Community 126 - "input"
Cohesion: 0.29
Nodes (8): padding-x, input, $type, $value, focus-ring, padding-x, $type, $value

### Community 127 - "generation-preflight.mjs"
Cohesion: 0.30
Nodes (10): buildGenerationPreflight(), compactError(), execFileAsync, insertTarget(), normalizeTarget(), replaceTarget(), runGenerationPreflight(), sourceResolutionCache (+2 more)

### Community 128 - "ShadcnInstaller"
Cohesion: 0.20
Nodes (7): main(), Handle shadcn/ui component installation., ShadcnInstaller, Tests for shadcn_add.py, Test adding all components without config., Test initialization with default project root., Test getting installed components without config.

### Community 129 - ".check_shadcn_config"
Cohesion: 0.21
Nodes (6): Add all available shadcn/ui components. Args: overwrite: If True, overwrite…, List installed components. Returns: Tuple of (success, message with component…, Check if shadcn is initialized in project. Returns: True if components.json…, Get list of already installed components. Returns: List of installed component…, Read shadcn version from project package.json; fall back to a pinned default., Add shadcn/ui components. Args: components: List of component names to add…

### Community 130 - ".generate_config_string"
Cohesion: 0.20
Nodes (6): Generate configuration file content. Returns: Configuration file as string, Generate TypeScript configuration., Generate JavaScript configuration., Format plugins array for config. Validates each plugin name against a strict…, Add indentation to JSON string., Write configuration to file. Returns: Tuple of (success, message)

### Community 131 - "require-dev"
Cohesion: 0.18
Nodes (11): require-dev, fakerphp/faker, larastan/larastan, laravel/breeze, laravel/pail, laravel/pao, laravel/pint, laravel/sail (+3 more)

### Community 132 - "inject-brand-context.cjs"
Cohesion: 0.31
Nodes (10): extractColorsFromTable(), extractCoreAttributes(), extractHexColors(), extractImageStyle(), extractTypography(), extractVoice(), fs, generatePromptAddition() (+2 more)

### Community 133 - "embed-tokens.cjs"
Cohesion: 0.18
Nodes (8): args, fs, minimal, MINIMAL_TOKENS, path, projectRoot, tokensPath, wrapStyle

### Community 134 - "detect-csp.mjs"
Cohesion: 0.20
Nodes (10): detectCsp(), INLINE_HEADER_SIGNALS, LAYOUT_EXTS, MONOREPO_HELPER_SIGNALS, NUXT_ROUTE_RULES_SIGNALS, NUXT_SECURITY_SIGNALS, SCAN_EXTS, SKIP_DIRS (+2 more)

### Community 135 - "embed-prompt.mjs"
Cohesion: 0.20
Nodes (7): args, buf, crc32(), crcTable, file, pngChunk(), readMode

### Community 136 - "palette.mjs"
Cohesion: 0.24
Nodes (7): args, buildWeights(), hashUnit(), pickSeed(), seed, SEEDS, weightedPick()

### Community 137 - "patch"
Cohesion: 0.18
Nodes (6): Test adding components with overwrite flag., Test successful component addition., Test component addition with subprocess error., Test component addition when npx is not found., Test successful addition of all components., patch

### Community 138 - "test_tailwind_config_gen.py"
Cohesion: 0.22
Nodes (8): Tests for tailwind_config_gen.py, Reduce a generated TS/JS config to a bare assignable object so it can be handed…, Regression guard for the missing-comma bug between the ``theme`` block and…, The property preceding ``plugins`` must end with a comma (pure-Python check, so…, The emitted config parses as valid JS via ``node --check``., _strip_to_object(), TestGeneratedConfigIsValidJs, parametrize

### Community 140 - "._base_config"
Cohesion: 0.22
Nodes (6): Any, Path, Initialize generator. Args: typescript: If True, generate .ts config, else .js…, Determine default output path., Create base configuration structure., Get default content paths for framework.

### Community 141 - "logo/generate.py"
Cohesion: 0.29
Nodes (9): enhance_prompt(), generate_batch(), generate_logo(), load_env(), main(), Enhance the logo prompt with style and industry modifiers, Generate a logo using Gemini models with image generation Args: aspect_ratio:…, Generate multiple logo variants with different styles (+1 more)

### Community 142 - "generate-tokens.cjs"
Cohesion: 0.36
Nodes (9): flattenTokens(), fs, generateCSS(), generateTailwind(), main(), parseArgs(), path, resolveReference() (+1 more)

### Community 143 - "button"
Cohesion: 0.20
Nodes (10): fg, font-size, hover-bg, button, $type, $value, $type, $value (+2 more)

### Community 144 - "duration"
Cohesion: 0.20
Nodes (10): fast, normal, slow, $type, $value, $type, $value, duration (+2 more)

### Community 145 - "staleness-notice.mjs"
Cohesion: 0.38
Nodes (9): appendStalenessDirective(), buildStalenessDirective(), cachePath(), filterFreshFindings(), pruneCache(), readCache(), readJson(), stalenessCheckDisabled() (+1 more)

### Community 147 - "syncEditBadgeHitProxies"
Cohesion: 0.27
Nodes (10): bindEditBadgeProxy(), editBadgeProxyTargets(), initEditBadge(), initEditBadgeHitProxies(), positionEditBadge(), proxyMouseEvent(), setImportantStyle(), styleEditBadgeProxy() (+2 more)

### Community 149 - "AppServiceProvider"
Cohesion: 0.25
Nodes (4): AppServiceProvider, AuthServiceProvider, Illuminate\Foundation\Support\Providers\AuthServiceProvider, Illuminate\Support\ServiceProvider

### Community 150 - "_run"
Cohesion: 0.28
Nodes (8): CompletedProcess, Path, Regression tests for validate-tokens.cjs. The validator used to skip any line…, A hardcoded hex on the same line as a var() token is still a violation., A line that references only tokens produces no false positives., _run(), test_flags_hardcoded_hex_sharing_line_with_token(), test_token_only_line_reports_no_violation()

### Community 151 - "sync-brand-to-tokens.cjs"
Cohesion: 0.33
Nodes (8): adjustBrightness(), { execFileSync }, extractColorsFromMarkdown(), fs, generateColorScale(), main(), path, updateDesignTokens()

### Community 152 - "template-extensions.mjs"
Cohesion: 0.33
Nodes (7): extensionCache, LIVE_TEMPLATE_EXTENSIONS, mergeExtensions(), normalizeExtensionEntries(), readLiveTemplateExtensions(), resolveLiveTemplateExtensions(), safeReadJson()

### Community 153 - "Illuminate\Foundation\Http\FormRequest"
Cohesion: 0.05
Nodes (15): StoreKostRequest, UpdateKostRequest, LoginRequest, RegistrationRequest, ResetPasswordRequest, AvatarUploadRequest, ProfileUpdateRequest, Illuminate\Auth\Events\Lockout (+7 more)

### Community 154 - "setup"
Cohesion: 0.25
Nodes (8): post-root-package-install, setup, composer install, npm install --ignore-scripts, npm run build, @php artisan key:generate, @php artisan migrate --force, @php -r \"file_exists('.env') || copy('.env.example', '.env');\

### Community 155 - "source-lock.mjs"
Cohesion: 0.50
Nodes (7): isLiveServerPidReachable(), clearStaleLock(), readLock(), releaseOwnLock(), sleepSync(), sourceLockPath(), withSourceLockSync()

### Community 156 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 157 - "checkHeadingRhythmDOM"
Cohesion: 0.18
Nodes (16): checkHeadingRhythmDOM(), clusterTop(), edgeAbove(), edgeBelow(), hasOwnTopBoundary(), insideSmallCard(), isVisibleFlow(), overlapsX() (+8 more)

### Community 158 - "detect_domain"
Cohesion: 0.43
Nodes (3): detect_domain(), Auto-detect the most relevant domain from query. Matches are weighted by…, TestDomainDetection

### Community 159 - "_select_palette_for_mode"
Cohesion: 0.43
Nodes (3): Pick the highest-ranked palette matching the resolved mode. Only the dark case…, _select_palette_for_mode(), TestPaletteSelection

### Community 165 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 166 - "logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 167 - "$type"
Cohesion: 0.60
Nodes (5): $type, $value, border, border, border

### Community 168 - "radius"
Cohesion: 0.60
Nodes (5): radius, radius, radius, $type, $value

### Community 169 - "lg"
Cohesion: 0.60
Nodes (5): lg, $type, $value, lg, lg

### Community 172 - "profile/edit.blade.php"
Cohesion: 0.40
Nodes (4): profile.partials.delete-user-form, profile.partials.update-avatar-form, profile.partials.update-password-form, profile.partials.update-profile-information-form

### Community 177 - "require"
Cohesion: 0.50
Nodes (4): require, laravel/framework, laravel/tinker, php

### Community 178 - "post-create-project-cmd"
Cohesion: 0.50
Nodes (4): post-create-project-cmd, @php artisan key:generate --ansi, @php artisan migrate --graceful --ansi, @php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\

### Community 179 - ".opencode/opencode.json"
Cohesion: 0.50
Nodes (3): plugin, $schema, .opencode/plugins/graphify.js

### Community 180 - "xl"
Cohesion: 0.67
Nodes (4): xl, xl, $type, $value

### Community 181 - "none"
Cohesion: 0.67
Nodes (4): $type, $value, none, none

### Community 182 - "detect.mjs"
Cohesion: 0.50
Nodes (3): candidates, detectorPath, __dirname

### Community 183 - "checkElementRadialSpotlightDOM"
Cohesion: 0.67
Nodes (4): checkElementRadialSpotlight(), checkElementRadialSpotlightDOM(), elementGradientValue(), spotlightLabel()

### Community 184 - "hook.mjs"
Cohesion: 0.83
Nodes (3): isStopEvent(), main(), readStdin()

### Community 185 - "provider.mjs"
Cohesion: 0.50
Nodes (3): IMPECCABLE_COMMAND, IMPECCABLE_COMMAND_PREFIX, IMPECCABLE_PROVIDER_ID

### Community 186 - "validate_data.py"
Cohesion: 0.83
Nodes (3): _check_file(), main(), _read_rows()

### Community 188 - "extra"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

### Community 194 - "16"
Cohesion: 0.67
Nodes (3): $type, $value, 16

### Community 195 - "1"
Cohesion: 0.67
Nodes (3): $type, $value, 1

### Community 196 - "3"
Cohesion: 0.67
Nodes (3): $type, $value, 3

### Community 197 - "8"
Cohesion: 0.67
Nodes (3): $type, $value, 8

### Community 198 - "destructive-foreground"
Cohesion: 0.67
Nodes (3): destructive-foreground, $type, $value

### Community 199 - "muted"
Cohesion: 0.67
Nodes (3): muted, $type, $value

### Community 201 - "primary-foreground"
Cohesion: 0.67
Nodes (3): primary-foreground, $type, $value

### Community 202 - "ring"
Cohesion: 0.67
Nodes (3): ring, $type, $value

### Community 203 - "secondary-foreground"
Cohesion: 0.67
Nodes (3): secondary-foreground, $type, $value

## Knowledge Gaps
- **456 isolated node(s):** `$type`, `$value`, `$type`, `$value`, `$type` (+451 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **33 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `Illuminate\Http\RedirectResponse`, `Kost`, `Illuminate\Database\Eloquent\Factories\Factory`, `Category`, `KostDocumentRequirement`, `TestCase`, `InvalidKostTransitionException`, `OtpVerification`, `OtpVerificationMail`, `Illuminate\Foundation\Http\FormRequest`?**
  _High betweenness centrality (0.021) - this node is a cross-community bridge._
- **Why does `Kost` connect `Kost` to `Illuminate\Http\RedirectResponse`, `User`, `Illuminate\Database\Eloquent\Factories\Factory`, `Category`, `KostDocumentRequirement`, `InvalidKostTransitionException`, `TestCase`, `AppServiceProvider`, `OtpVerificationMail`, `Illuminate\Foundation\Http\FormRequest`?**
  _High betweenness centrality (0.015) - this node is a cross-community bridge._
- **Why does `enterLiveRoot()` connect `roots.mjs` to `live-accept.mjs`, `live-inject.mjs`, `live-wrap.mjs`, `live-poll.mjs`, `live-status.mjs`, `session-store.mjs`, `live-server.mjs`?**
  _High betweenness centrality (0.004) - this node is a cross-community bridge._
- **Are the 2 inferred relationships involving `TailwindConfigGenerator` (e.g. with `TestGeneratedConfigIsValidJs` and `TestTailwindConfigGenerator`) actually correct?**
  _`TailwindConfigGenerator` has 2 INFERRED edges - model-reasoned connections that need verification._
- **What connects `$type`, `$value`, `$type` to the rest of the system?**
  _456 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\Http\RedirectResponse` be split into smaller, more focused modules?**
  _Cohesion score 0.038901601830663615 - nodes in this community are weakly interconnected._
- **Should `Kost` be split into smaller, more focused modules?**
  _Cohesion score 0.027616279069767442 - nodes in this community are weakly interconnected._