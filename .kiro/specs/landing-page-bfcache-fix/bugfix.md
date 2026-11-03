# Landing Page BFCache Transform Fix - Requirements

## Bug Summary

The landing page contains massive inline CSS with hover transforms (translateY, translateX) that get cached by the browser's back-forward cache (bfcache). When users navigate back to the landing page using the browser back button, these transforms remain applied from the cached state, causing the layout to render in a broken state with elements offset from their intended positions.

## Problem Statement

Users report that when they:
1. Visit the landing page
2. Hover over buttons or interactive elements (triggering transform animations)
3. Navigate to another page
4. Use the browser back button to return to the landing page

The page loads with CSS transforms still applied from the bfcache, resulting in:
- Elements positioned incorrectly (offset vertically or horizontally)
- Broken layout appearance
- Visual misalignment of buttons, cards, and other interactive elements

## Current Situation

### Context
- **File**: `templates/landing/index.html.twig`
- **Issue**: Massive inline CSS with hover transforms cached by bfcache
- **Affected Elements**: Navigation buttons, hero buttons, quick link items, process cards, video container, certificate items
- **Symptom**: Layout breaks when page is restored from bfcache

### Previous Attempts
- Server-side cache headers were added to disable caching (Cache-Control: no-store), but this doesn't prevent bfcache
- JavaScript-only fixes didn't work because the CSS is embedded and cached with the page itself
- The root cause is the CSS being embedded inline with transform states that persist in the cache

### Why Previous Solutions Failed
1. **Cache-Control headers** don't prevent bfcache - the browser still caches the DOM and all its computed styles
2. **JavaScript-only solutions** can't override CSS that's already cached and applied
3. **The CSS transforms are embedded inline in the template**, so they're part of the cached page state

## Requirements

### 1. Functional Requirements

**1.1 Transform State Clearing**
- The fixed solution SHALL ensure that all CSS transforms are reset to their default state (none) when the page is loaded or restored from bfcache
- No transform states from previous visits should persist
- This must occur reliably before any rendering occurs

**1.2 Visual Consistency**
- The landing page SHALL render with identical visual appearance whether accessed fresh or restored from bfcache
- All elements SHALL be positioned correctly on first paint
- No layout shifts or jumps should occur after initial render

**1.3 Existing Functionality Preservation**
- All existing hover effects and CSS animations SHALL continue to work as designed
- Visual interactivity (buttons, cards, video container, links) SHALL remain unchanged
- No reduction in user experience or visual polish

### 2. Technical Requirements

**2.1 Server-Side Agnostic**
- The solution SHALL NOT require changes to page generation logic or server-side rendering
- No modifications to Twig template inheritance or Symfony controller logic needed
- The fix should be purely client-side or CSS-based

**2.2 Browser Compatibility**
- The solution SHALL work reliably across all modern browsers (Chrome, Firefox, Safari, Edge)
- Must handle bfcache behavior consistently across browsers
- Specific attention to Safari's aggressive bfcache behavior

**2.3 CSS Structure**
- The solution SHALL address the root cause: inline CSS with hover transforms
- Should prevent problematic caching patterns at the CSS level
- Consider separating CSS from template to enable proper cache control

### 3. Design Requirements

**3.1 Minimal Disruption**
- Changes should be minimal and focused on the transform caching issue
- Existing CSS animations and transitions should not be affected
- No major refactoring of template structure unless necessary

**3.2 Performance**
- No degradation of page load performance
- No unnecessary additional HTTP requests unless they provide significant benefit
- Clean rendering without layout thrashing

**3.3 Maintainability**
- Solution should be easy to understand and maintain
- Clear comments explaining why the fix is necessary
- No complex workarounds or hacks that will cause confusion later

## Acceptance Criteria

**2.1 When a user navigates back to the landing page using browser back button, the page SHALL render with all CSS transforms in their default state**
- Transforms should be reset to `transform: none` on page load/restore
- No visual offset of elements from their intended positions
- Layout should be identical to first-time load

**2.2 All existing hover effects and interactive elements SHALL continue to work correctly**
- Buttons should still lift up (translateY) on hover
- Quick link items should still respond to hover transforms
- Video container should still animate on hover
- Process cards and certificate items should maintain their hover states

**2.3 The fix SHALL work when users navigate back using the browser back button**
- Chrome, Firefox, Safari, and Edge should all restore the page with clean transforms
- No JavaScript errors in console
- No performance degradation

**2.4 Existing functionality and visual design SHALL be completely maintained**
- No reduction in CSS animation quality
- No changes to color schemes, spacing, or typography
- All responsive breakpoints should work as before
- Accessibility features should not be negatively affected

## Technical Context

### Root Cause Analysis (Initial Hypothesis)

The browser's bfcache preserves the DOM tree and all associated computed styles, including applied CSS transforms. When hover transforms are applied via inline CSS, they become part of the cached page state. Upon restoration:

1. The browser restores the cached DOM with all its computed styles
2. CSS transforms that were applied during hover remain in effect
3. The page renders with these transforms still active
4. Elements appear offset from their intended positions

### Solution Approach (High Level)

Possible solutions to investigate:

1. **CSS Reset on Page Load**: Add CSS/JavaScript to forcibly reset all transforms on page load
2. **Separate External CSS**: Move CSS to external file with appropriate cache headers to prevent problematic caching
3. **CSS Custom Properties**: Use CSS variables for transforms that can be reset via JavaScript
4. **Service Worker Strategy**: Intercept bfcache restoration and inject reset code
5. **Page Lifecycle Events**: Use `pageshow` event to detect bfcache restoration and reset transforms

## Success Metrics

- ✅ Landing page displays correctly when loaded fresh
- ✅ Landing page displays correctly when restored from bfcache
- ✅ No transform artifacts or layout offsets on restoration
- ✅ All hover effects still work as designed
- ✅ No visual quality reduction
- ✅ Works across all major browsers
- ✅ No server-side changes required

## Notes

- The `Cache-Control: no-store` header in `LandingController.php` doesn't prevent bfcache
- BFcache is separate from HTTP caching - it caches the DOM state itself
- The fix must address the CSS transforms specifically, not just HTTP caching
- The massive inline CSS in the template is the root of the issue and should ideally be separated
