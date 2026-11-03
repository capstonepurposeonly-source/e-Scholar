# Landing Page BFCache Fix Design

## Overview

The landing page contains massive inline CSS (over 1000 lines) with hover transforms (translateY, translateX) that get cached by the browser's back-forward cache (bfcache). When users navigate back to the page using the browser back button, these transforms remain applied from the cached state, causing elements to render offset from their intended positions.

This design document outlines a strategy to prevent transform states from being cached in a broken state, ensuring the landing page renders correctly every time it's loaded or restored from bfcache, while maintaining all existing functionality and visual design without requiring server-side changes.

## Glossary

- **Bug_Condition (C)**: The condition that triggers the bug - when CSS transforms remain applied after page restoration from bfcache
- **Property (P)**: The desired behavior when the page is loaded or restored - all CSS transforms should be in their default state (transform: none)
- **Preservation**: Existing hover effects and CSS animations must continue to work normally for non-cached pageloads
- **BFCache**: Browser's back-forward cache - caches the DOM tree and all computed styles when a page is navigated away from
- **Transform State**: CSS transforms applied to elements (translateY, translateX, etc.) that can persist in bfcache
- **pageshow Event**: Fired when page is shown, either from fresh load or from bfcache restoration
- **handlePageRestore()**: The function in client-side code that will detect bfcache restoration and reset transforms

## Bug Details

### Bug Condition

The bug manifests when a user navigates back to the landing page using the browser back button after the page has been loaded and interactive elements with hover transforms have been interacted with. The landing page's massive inline CSS contains multiple hover transform rules that become part of the cached page state. Upon bfcache restoration, these cached transforms remain applied to elements, causing them to render with incorrect positioning.

**Formal Specification:**
```
FUNCTION isBugCondition(navigationEvent)
  INPUT: navigationEvent of type PageRestoreEvent
  OUTPUT: boolean
  
  RETURN navigationEvent.type == 'pageshow'
         AND navigationEvent.persisted == true
         AND existsAppliedTransforms(document.body)
         AND transformsNotReset()
END FUNCTION
```

### Examples

**Example 1: Button Hover Transform Remains Applied**
- User visits landing page in fresh state
- User hovers over a hero button (transform: translateY(-2px) applied)
- User clicks a link to navigate to another page
- User presses browser back button
- **Current behavior**: Button appears offset 2px upward from its intended position
- **Expected behavior**: Button appears at correct position with no vertical offset

**Example 2: Quick Link Items Stuck with Transform**
- User visits landing page
- User hovers over quick link items (transform: translateY(-3px) applied)
- User navigates away
- User returns using back button
- **Current behavior**: Quick link items appear 3px higher than they should be
- **Expected behavior**: Quick link items appear at correct vertical position

**Example 3: Multiple Elements with Stacked Transforms**
- User hovers over multiple interactive elements during page session
- Different elements have different transform values applied
- Page is navigated away and user returns with back button
- **Current behavior**: Multiple elements appear offset, creating layout breakage
- **Expected behavior**: All elements return to default positions with transform: none

**Edge Case: Fresh Page Load (Non-Buggy Input)**
- User navigates to landing page directly or with fresh cache
- **Expected behavior**: Page loads normally with no transforms applied initially
- This should continue to work identically after the fix

## Expected Behavior

### Preservation Requirements

**Unchanged Behaviors:**
- Hover effects on buttons must continue to lift up (translateY) when user hovers
- Quick link items must still animate upward on hover with proper shadows
- Video container must continue to scale and animate on hover
- Process cards must maintain their transform animations on hover
- All CSS transitions and animations must remain smooth
- Visual design and styling must be completely unchanged
- All color schemes, spacing, and typography must remain identical
- Responsive breakpoints must work as before
- Mobile menu toggle and navigation must function normally
- Page scrolling and smooth scroll behavior must be preserved

**Scope:**
All inputs that do NOT involve bfcache restoration (fresh pageloads, direct navigation, same-page interactions) should be completely unaffected by this fix. This includes:
- First-time visits to the landing page
- Direct navigation via URL
- Page reloads with Ctrl+R or F5
- Navigation via navigation links that don't trigger bfcache
- All hover, click, and keyboard interactions on the page itself

## Hypothesized Root Cause

Based on the bug description and code analysis, the most likely causes are:

1. **BFCache Caching Computed Styles**: The browser's bfcache preserves not just the HTML but also all computed CSS styles, including applied transforms. When a user hovers over elements with transform animations, these computed styles become part of the cached page state.

2. **Transforms Not Reset on Pageshow**: When the page is restored from bfcache, there's no mechanism to detect restoration and reset the cached transforms. The `pageshow` event fires with `persisted: true`, but without an event listener, transforms remain applied.

3. **No Differentiation Between Hover State and Normal State**: The CSS doesn't explicitly reset transforms when the page loads or is restored. Transforms applied during hover simply persist because there's no code to clear them.

4. **Inline CSS Cannot Be Re-parsed**: Because the CSS is embedded inline in the template, it can't be re-evaluated or refreshed on bfcache restoration. It's cached as-is with all its applied transforms.

5. **HTTP Cache Headers Don't Affect BFCache**: The `Cache-Control: no-store` header in the controller doesn't prevent bfcache. BFCache is a separate mechanism that stores the DOM state itself, not just HTTP responses.

## Correctness Properties

Property 1: Bug Condition - BFCache Transform Reset

_For any_ page navigation event where the page is restored from bfcache (pageshow event with persisted=true), the fixed landing page SHALL ensure all CSS transforms are reset to their default state (transform: none), causing all elements to appear at their correct positions without any offset or layout distortion.

**Validates: Requirements 2.1, 2.2**

Property 2: Preservation - Hover Effects Continue Working

_For any_ page load or interaction that is NOT a bfcache restoration (fresh pageloads, direct navigation, user hover actions), the fixed code SHALL produce exactly the same behavior as the original code, preserving all hover transforms, CSS animations, visual effects, and interactive functionality.

**Validates: Requirements 2.3, 3.1, 3.2, 3.3**

## Fix Implementation

### Changes Required

Assuming our root cause analysis is correct, the fix involves detecting bfcache restoration and resetting all transforms on the page.

**File**: `templates/landing/index.html.twig`

**Changes**:

1. **Add pageshow Event Listener**: Add JavaScript code before closing body tag to listen for pageshow events and reset transforms when page is restored from bfcache

```html
<script>
  document.addEventListener('pageshow', function(event) {
    if (event.persisted) {
      // Page was restored from bfcache
      resetAllTransforms();
    }
  });

  function resetAllTransforms() {
    // Reset all elements to clear cached transforms
    document.querySelectorAll('*').forEach(element => {
      element.style.transform = 'none';
    });
    // Force layout recalculation
    void document.body.offsetHeight;
  }
</script>
```

2. **Add CSS Reset Override**: Add to the inline style block to ensure transforms default to none and have an explicit reset mechanism:

```css
/* BFCache Fix: Ensure transforms are reset on page load/restore */
* {
  transform: none !important;
}

@media (prefers-reduced-motion: no-preference) {
  /* Allow transforms only on interactive states */
  button:hover,
  .quick-link-item:hover,
  .video-container:hover,
  .process-step:hover,
  .certificate-item:hover {
    /* Hover transforms are allowed here */
  }
}
```

3. **Alternative: Separation of CSS (Optional but Recommended)**: Move inline CSS to external file with proper cache headers to prevent CSS from being embedded and cached as part of the page:

**File**: `public/css/landing.css` (new file)
- Move the massive inline CSS to this external file
- Update controller to set `Cache-Control: max-age=3600` or similar for CSS file
- This provides multiple benefits:
  - CSS can be cached separately from page
  - CSS won't be bfcached as part of the page
  - Multiple pages can share the CSS file
  - Easier to update CSS without page changes

### Specific Implementation Strategy

**Phase 1 - Quick Fix (Immediate)**: Add pageshow event listener to reset transforms when bfcache restoration is detected

**Phase 2 - Long-term Solution (Recommended)**: Separate CSS into external file to prevent inline CSS from being embedded in bfcache

### Why This Works

1. **Detects BFCache Restoration**: The `pageshow` event with `persisted: true` specifically indicates bfcache restoration
2. **Clears Transform State**: Resetting all transforms on restoration ensures elements appear at correct positions
3. **Minimal Code**: Simple JavaScript snippet requires no major refactoring
4. **No Server Changes**: Works entirely client-side without modifying Twig or Symfony code
5. **Preserves Functionality**: Doesn't affect hover effects - they rebuild when user interacts
6. **Cross-Browser Compatible**: pageshow event is widely supported across all modern browsers

## Testing Strategy

### Validation Approach

The testing strategy follows a two-phase approach: first, surface counterexamples that demonstrate the bug on unfixed code by simulating bfcache restoration and observing transform persistence, then verify the fix works correctly and preserves existing hover behavior.

### Exploratory Bug Condition Checking

**Goal**: Surface counterexamples that demonstrate the bug BEFORE implementing the fix. Confirm or refute the root cause analysis. If we refute, we will need to re-hypothesize.

**Test Plan**: Write tests that simulate the `pageshow` event with `persisted: true` on the UNFIXED code to observe that transforms remain applied. This confirms that bfcache restoration doesn't automatically reset transforms.

**Test Cases**:
1. **Simulate BFCache Restoration**: Fire `pageshow` event with `persisted: true` on unfixed code and check that transforms from previous hover actions are still applied (will fail on unfixed code - transforms remain)
2. **Check Transform Persistence**: After hovering over buttons and firing pageshow event, verify that elements still have applied transforms (will demonstrate bug)
3. **Verify Multiple Elements**: Confirm that multiple hovered elements all retain their transforms after bfcache simulation (will fail on unfixed code)
4. **Edge Case - Fresh Load**: Simulate fresh pageload (pageshow with persisted: false) and verify no transforms are applied initially (will pass on both fixed and unfixed)

**Expected Counterexamples**:
- When `pageshow` event fires with `persisted: true`, previously applied transform styles remain on elements
- Elements appear offset from their intended positions
- No automatic mechanism clears these transforms on restoration
- Possible causes: bfcache preserves computed styles, no event listener to detect restoration and reset, CSS transitions don't fire on restoration

### Fix Checking

**Goal**: Verify that for all inputs where the bug condition holds (bfcache restoration), the fixed function produces the expected behavior (transforms are reset).

**Pseudocode:**
```
FOR ALL event WHERE isBugCondition(event) DO
  // event is pageshow with persisted: true
  resetAllTransforms()  // This is called by event listener
  result := checkAllElementTransforms()
  ASSERT EVERY element.style.transform == 'none' OR element.style.transform == ''
  ASSERT NO elements have visual offset
END FOR
```

### Preservation Checking

**Goal**: Verify that for all inputs where the bug condition does NOT hold (fresh pageloads, user hover actions), the fixed code produces the same result as the original code.

**Pseudocode:**
```
FOR ALL interaction WHERE NOT isBugCondition(interaction) DO
  ASSERT hoverEffects(interaction) == originalHoverEffects(interaction)
  ASSERT visualAppearance(interaction) == originalVisualAppearance(interaction)
  ASSERT cssAnimations(interaction) == originalCssAnimations(interaction)
END FOR
```

**Testing Approach**: Property-based testing is recommended for preservation checking because:
- It generates many test scenarios automatically across different input types
- It catches edge cases where hover effects might be unexpectedly broken
- It provides strong guarantees that all non-bfcache interactions remain identical
- It verifies hover transforms still work correctly on fresh pageloads

**Test Plan**: Observe behavior on UNFIXED code first for hover effects and fresh pageloads, then write property-based tests to verify this behavior is preserved after fix is applied.

**Test Cases**:
1. **Hover Transforms Preserved**: After fix, hovering over buttons still applies transforms correctly and creates lift effect
2. **Fresh Pageload Behavior**: Fresh pageload of landing page works identically before and after fix
3. **Multiple Hover Interactions**: Multiple sequential hover interactions work correctly and don't cause layout issues
4. **Transform Cleanup**: When user moves mouse away from elements, transforms revert to original state
5. **CSS Transitions Smooth**: All CSS transitions and animations remain smooth and perform identically

### Unit Tests

- Test that `pageshow` event with `persisted: true` triggers transform reset
- Test that all elements have transform: none after pageshow event fires
- Test that `pageshow` event with `persisted: false` does NOT trigger unnecessary reset
- Test that hover effects still trigger transforms after page loads fresh
- Test that element positioning is identical before and after fix for non-bfcache scenarios
- Test that no JavaScript errors occur when event listener runs

### Property-Based Tests

- Generate random hover sequences and verify transforms apply/remove correctly
- Generate random page load scenarios and verify visual appearance is identical
- Generate random navigation patterns including back button presses
- Test across different viewport sizes and responsive breakpoints
- Verify CSS animation performance is not degraded

### Integration Tests

- Full user flow: load landing page → hover over elements → navigate away → use browser back button → verify correct appearance
- Test in different browsers (Chrome, Firefox, Safari, Edge)
- Test on different devices and screen sizes
- Test with browser devtools open/closed
- Test with disabled JavaScript (graceful degradation - CSS still works, just transforms won't reset)
- Test with slow network conditions to ensure event listener loads and fires

### Manual Testing Checklist

- [ ] Load landing page fresh - verify no transforms applied
- [ ] Hover over multiple elements - verify transforms apply correctly
- [ ] Navigate to another page
- [ ] Use browser back button - verify landing page appears correctly with no transform offsets
- [ ] Test in Chrome
- [ ] Test in Firefox
- [ ] Test in Safari
- [ ] Test in Edge
- [ ] Test hover effects work after returning from back button
- [ ] Test on mobile/responsive view
- [ ] Verify no console errors in DevTools
