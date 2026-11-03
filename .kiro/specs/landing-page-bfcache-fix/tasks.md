# Landing Page BFCache Fix - Implementation Tasks

## Phase 1: Immediate Fix (Quick Solution)

### 1.1 Add PageShow Event Listener
- [ ] Open `templates/landing/index.html.twig`
- [ ] Locate the closing `</body>` tag before `{% endblock %}`
- [ ] Add pageshow event listener script that detects bfcache restoration and resets transforms
- [ ] Function should check `event.persisted` flag
- [ ] Reset all element transforms using `element.style.transform = 'none'`
- [ ] Force layout recalculation with `offsetHeight` property access
- [ ] Verify no JavaScript errors in browser console

**Related Design Section**: Fix Implementation - Changes Required #1

### 1.2 Add CSS Transform Reset Override
- [ ] Add CSS rule to inline style block: `* { transform: none !important; }`
- [ ] This ensures explicit default state for all elements
- [ ] Add comment explaining this is a bfcache fix
- [ ] Verify hover transforms still work after this addition

**Related Design Section**: Fix Implementation - Changes Required #2

### 1.3 Test Bug Condition Checking
- [ ] Simulate pageshow event with persisted: true in browser console
- [ ] Verify transforms are reset when event fires
- [ ] Test by hovering over elements, firing pageshow event, checking no offsets remain
- [ ] Verify visual appearance matches fresh pageload state

**Related Design Section**: Testing Strategy - Exploratory Bug Condition Checking

### 1.4 Test Preservation - Hover Effects
- [ ] Load landing page fresh
- [ ] Hover over buttons, quick links, video container, process cards, certificate items
- [ ] Verify all hover transforms still apply correctly
- [ ] Verify visual animations are smooth
- [ ] Verify no regression in existing functionality

**Related Design Section**: Testing Strategy - Preservation Checking

### 1.5 Manual Testing - Browser Back Button Flow
- [ ] Load landing page
- [ ] Hover over several interactive elements
- [ ] Navigate to another page (Student Portal, Admin Login, or Programs)
- [ ] Use browser back button to return to landing page
- [ ] Verify landing page appears correctly with no element offsets
- [ ] Verify all interactive elements are positioned correctly

**Related Design Section**: Testing Strategy - Integration Tests

## Phase 2: Long-Term Solution (Optional - Recommended for Future)

### 2.1 Extract CSS to External File
- [ ] Create `public/css/landing.css` file
- [ ] Move entire inline CSS from `landing/index.html.twig` style block to external file
- [ ] Replace inline style block with single `<link>` tag
- [ ] Update controller to set appropriate cache headers for CSS file

**Related Design Section**: Fix Implementation - Separation of CSS (Optional)

### 2.2 Update Controller Cache Headers
- [ ] Modify `LandingController.php`
- [ ] Adjust cache headers based on new external CSS approach
- [ ] Ensure CSS file is cacheable while HTML isn't
- [ ] Document rationale for cache strategy

**Related Design Section**: Fix Implementation - Phase 2

### 2.3 Cross-Browser Testing - External CSS Version
- [ ] Test in Chrome with external CSS
- [ ] Test in Firefox with external CSS
- [ ] Test in Safari with external CSS
- [ ] Test in Edge with external CSS
- [ ] Verify bfcache behavior improvements with external CSS

## Phase 3: Verification and Validation

### 3.1 Property-Based Testing
- [ ] Write PBT: Generate random hover sequences, verify transforms reset correctly
- [ ] Write PBT: Generate random navigation patterns including back button
- [ ] Write PBT: Verify fresh pageload behavior unchanged
- [ ] Generate test reports showing coverage

**Related Design Section**: Testing Strategy - Property-Based Tests

### 3.2 Unit Test Suite
- [ ] Test pageshow event detection with persisted: true
- [ ] Test pageshow event handling with persisted: false (should not reset)
- [ ] Test all elements have transform: none after reset
- [ ] Test no JavaScript errors in event listener
- [ ] Test event listener doesn't interfere with initial pageload

**Related Design Section**: Testing Strategy - Unit Tests

### 3.3 Integration Test Execution
- [ ] Full user flow test: load → hover → navigate → back
- [ ] Multi-element hover test: hover over 5+ elements before navigating back
- [ ] Mobile responsive test: test on mobile viewport
- [ ] Keyboard navigation test: verify Tab and Enter work correctly
- [ ] Screen reader test: verify accessibility not affected

**Related Design Section**: Testing Strategy - Integration Tests

### 3.4 Browser Compatibility Verification
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile Chrome
- [ ] Mobile Safari

## Phase 4: Documentation and Cleanup

### 4.1 Add Code Comments
- [ ] Add comment explaining bfcache issue in event listener
- [ ] Add comment explaining transform reset rationale
- [ ] Reference bugfix design doc in comments

### 4.2 Update README or Docs
- [ ] Document the bfcache fix in code documentation
- [ ] Explain why inline CSS with transforms can cause bfcache issues
- [ ] Provide guidance for future modifications to landing page

### 4.3 Final Verification
- [ ] Run full test suite
- [ ] Verify no regressions
- [ ] Check browser console for errors/warnings
- [ ] Verify performance metrics haven't degraded
- [ ] Confirm all acceptance criteria met

## Acceptance Criteria Checklist

- [ ] **2.1**: When user navigates back using browser back button, page renders with all transforms reset (transform: none)
- [ ] **2.2**: All hover effects and interactive elements continue to work correctly
- [ ] **2.3**: Fix works in all major browsers (Chrome, Firefox, Safari, Edge)
- [ ] **2.4**: No reduction in existing functionality or visual design
- [ ] **Preservation**: Fresh pageloads work identically to before fix
- [ ] **Preservation**: Hover animations work identically to before fix
- [ ] **Preservation**: All CSS transitions remain smooth
- [ ] **Preservation**: Responsive design unchanged
- [ ] No JavaScript errors in console
- [ ] No performance degradation
- [ ] Server-side code unchanged (no Twig or Symfony modifications needed)

## Notes

- The immediate fix (Phase 1) should resolve the bug completely
- Phase 2 (external CSS) is recommended for long-term maintainability and performance
- BFCache is browser-controlled and cannot be disabled - the fix must work with bfcache behavior
- The pageshow event is the standard way to detect bfcache restoration across all browsers
- Tests should be run on real browsers, not just headless tests, to verify bfcache behavior
