# Events Plugin — MVP Spec (Version 1)

## Purpose

A lightweight WordPress plugin for creating and displaying events. This is a deliberately trimmed-down first build: create an event in wp-admin, display it on the front end. No RSVP, no member gating, no CSV import, no payments.

This MVP is the foundation of the same modular system originally scoped for the full core plugin (Saltburn public events portal + future Autism Matters booking system). The hooks below are carried over from that architecture so later extensions — RSVP, Members, Payments — can be added without rebuilding this plugin.

Initial use case: a simple public events listing for Saltburn at happening.co.uk.

---

## Scope

**In scope for this build:**
- Custom post type `event`
- Event fields: date, time, location, description (standard post editor body), optional featured image
- Events list (archive template and/or `[events_list]` shortcode) — sorted soonest-first, past events hidden automatically
- Single event page using standard WordPress template hierarchy
- Admin meta box for entering date/time/location on the event edit screen

**Explicitly out of scope for this build (but not blocked):**
- RSVP / booking functionality
- Member-only access or login-gating
- CSV import
- Payments
- Email notifications
- Custom database tables

---

## Architecture Principles

Same core + extension pattern as the original spec: this plugin must remain standalone and fully functional on its own, and must never contain member-specific or payment-specific logic directly. Instead, it exposes hooks and filters so future extensions can inject behaviour cleanly.

### Hooks Required for Future Extensions

These must exist and be documented in code even though nothing uses them yet:

- `events_plugin_can_view_event` (filter) — return true/false for whether the current user can see this event.
- `events_plugin_can_rsvp` (filter) — return true/false for whether the current user can RSVP. Reserved for the future RSVP extension.
- `events_plugin_rsvp_form_fields` (filter) — modify RSVP form fields. Reserved for the future RSVP extension.
- `events_plugin_rsvp_created` (action) — fires after a successful RSVP. Reserved for the future RSVP extension.
- `events_plugin_event_query_args` (filter) — modify the WP_Query arguments used to build event lists (e.g. region, capacity). **Used in this MVP** to implement "hide past events" and sort order, so it's exercised from day one rather than just stubbed.
- `events_plugin_event_display_data` (filter) — modify event data before display (date/time/location/etc.) before it's rendered into a template.

---

## Plugin Structure

```
events-plugin/
├── events-plugin.php       (main plugin file, headers, activation/deactivation)
├── README.md
├── includes/
│   ├── class-event-post-type.php   (registers the 'event' CPT)
│   ├── class-event-meta-box.php    (admin meta box: date, time, location)
│   └── class-event-query.php       (query modification via events_plugin_event_query_args)
├── templates/
│   ├── archive-event.php    (events list template)
│   └── single-event.php     (single event template)
└── assets/
    └── events-plugin.css    (minimal base styling)
```

---

## Custom Post Type: `event`

- Slug: `event`
- Public, with archive enabled (`has_archive => true`)
- Supports: `title`, `editor` (description), `thumbnail` (featured image)
- Not hierarchical
- Menu icon: WordPress dashicon, e.g. `dashicons-calendar-alt`

### Meta Fields (via meta box, stored as post meta)

| Field | Meta key | Type | Notes |
|---|---|---|---|
| Event date | `_event_date` | date | Required |
| Event time | `_event_time` | time | Optional |
| Location | `_event_location` | text | Free text, e.g. "Saltburn Community Hall" |

Meta box appears on the event edit screen, saved via standard `save_post` hook with a nonce check.

---

## Query Behaviour

- Event list (archive and shortcode) sorted by `_event_date` ascending (soonest first).
- Events with `_event_date` in the past are excluded automatically from the list.
- This filtering happens via the `events_plugin_event_query_args` filter so any future extension can further restrict the query (e.g. by member region) without touching this plugin's core code.

---

## Front End

### Events List
- Available two ways: the CPT archive template (`templates/archive-event.php`, loaded via `template_include`) and a `[events_list]` shortcode for placing the list anywhere (e.g. a page).
- Each list item shows: title, date, time, location, featured image (if set), link to single event.

### Single Event
- Standard WordPress template hierarchy (`templates/single-event.php`, loaded via `template_include`).
- Shows: title, date, time, location, featured image, full description (post content).

### Styling
- Minimal, unopinionated CSS in `assets/events-plugin.css` — enough to be presentable, not a full design system. Deliberately left flexible for later iteration.

---

## Build Order

1. Scaffold plugin file (`events-plugin.php`) with plugin headers, activation hook to flush rewrite rules.
2. Register the `event` custom post type.
3. Add admin meta box for date/time/location, with save handling and nonce check.
4. Implement query modification (sort ascending by date, exclude past events) via `events_plugin_event_query_args` filter.
5. Build archive template for the events list.
6. Build `[events_list]` shortcode (reuses the same query/render logic as the archive template).
7. Build single event template.
8. Add base CSS styling.
9. Manual test: create 3–4 events with mixed past/future dates, confirm sorting and past-event hiding work correctly on both archive and shortcode.

Commit after each step. If you want to pause and review, do it after steps 3, 6, and 9.

---

## Success Criteria for MVP

- You can create an event in wp-admin, including date/time/location, in under 2 minutes.
- The events list (archive or shortcode) shows only upcoming events, soonest first.
- Clicking an event goes to a working single event page with all details.
- Plugin is structured so RSVP/Members/Payments extensions can be added later without modifying these files, only hooking into the six filters/actions above.

---

## What's Deliberately Left Flexible

- Exact visual styling and layout
- Whether the shortcode or the archive template becomes the "main" way events are displayed on the live site
- Any additional taxonomy (e.g. event categories) — not needed for MVP, easy to add later without breaking this spec
