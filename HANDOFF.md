# HANDOFF LOG
Generated : 2026-05-13 07:35
AI_Model  : claude-opus-4-7
Project   : newScience (CodeIgniter 4 — Faculty of Science portal)

---

## CONTEXT
Redesigning the **E-document module** (`/edoc/...`) into a Gmail-style inbox for end users: sidebar labels, category tabs, per-user star/read/archive, custom user labels, forward-to-user without duplicating files. Currently in **Phase A — schema + models only**; UI not started. Hard constraint: users cannot delete documents, and forwarding must NOT duplicate the file in `edoctitle.fileaddress`.

---

## DONE ✓
- `database/edoc_gmail_layout.sql` — created 4 new tables: `edoc_user_labels`, `edoc_document_labels`, `edoc_user_flags`, `edoc_forwards` (additive — no edits to `edoctitle` / `edoc_document_tags`)
- `app/Models/Edoc/EdocUserLabelModel.php` — CRUD per-user labels (listForUser/createForUser/renameForUser/deleteForUser)
- `app/Models/Edoc/EdocDocumentLabelModel.php` — apply/remove label, getLabelsForDocument(s), getDocumentIdsByLabel
- `app/Models/Edoc/EdocUserFlagModel.php` — getFlags, setFlag, toggleStar, markRead, archive (upsert pattern)
- `app/Models/Edoc/EdocForwardModel.php` — logForward, getForwardsTo, getForwardForUser, getForwardedDocumentIds

---

## PROBLEMS → SOLUTIONS
- Naming collision risk: existing `edoctag` table is actually a **person/recipient registry**, not category tags. → New "category-style" tags use `edoc_user_labels` (per-user, Gmail-style) to avoid overloading the term. [FIXED]
- Forward without duplicating storage: → Forward = INSERT into `edoc_forwards` (log) + append recipient to existing `edoc_document_tags` (which `EdocController::getDoc()` already uses for visibility queries). The single file at `edoctitle.fileaddress` is reused. [DESIGN-LOCKED]

---

## CURRENT STATE
- STATUS: Phase A + B + C + D + E complete. Phase F (polish) remains.
- BUILD : N/A (PHP). **SQL not yet applied to DB** — must run `database/edoc_gmail_layout.sql` first.
- BRANCH: master
- DB    : `edoc_gmail_layout.sql` still needs to be executed against the database.
- ROUTES: All inbox/labels/documents/forward routes added to `app/Config/Routes.php`.
- SYNTAX: All PHP files pass `php -l` check.

---

## PENDING ⏳
1. **[CRITICAL] Run migration**: `mysql -u root -p newscience < database/edoc_gmail_layout.sql`
2. **Phase F — Polish**: 
   - Add keyboard shortcuts (j/k navigate rows, s = star, e = archive)
   - Mobile responsive: collapse sidebar to bottom nav on small screens
   - Unread count badge auto-refresh after mark-read
   - Forward banner in `document_view.php`: if user has row in `edoc_forwards.to_email` for that doc, show "ส่งต่อโดย X — หมายเหตุ: ..."
3. **Optional**: Add link from old `showEdoc.php` hero bar → "ลองใหม่: กล่องเอกสาร" button pointing to `/edoc/inbox`
4. **Test end-to-end**: open `/edoc/inbox`, verify doc list loads, star/archive/label/forward all work

---

## KEY FILES
- `database/edoc_gmail_layout.sql` → new schema (needs to be applied to DB)
- `app/Models/Edoc/EdocUserLabelModel.php` → per-user labels CRUD
- `app/Models/Edoc/EdocDocumentLabelModel.php` → doc↔label mapping (per user)
- `app/Models/Edoc/EdocUserFlagModel.php` → per-user star/read/archive/important
- `app/Models/Edoc/EdocForwardModel.php` → forward log
- `app/Models/Edoc/EdocDocumentTagModel.php` → **existing** — already used by `getDoc()` for visibility; forward should append rows here too
- `app/Controllers/Edoc/EdocController.php` → contains `getDoc()` (line ~271) which is the reference query pattern. Visibility is via `edoctitle.owner = userEmail OR edoctitle.participant LIKE %userEmail%`. New `inboxData()` should keep this WHERE clause and add LEFT JOIN to flags/labels.
- `app/Views/edoc/documents/showEdoc.php` → current DataTable UI (keep as fallback)
- `app/Views/edoc/documents/document_view.php` → single-doc view; forward banner goes here

### Critical data model facts
- `edoctitle.iddoc` = primary key for documents (INT UNSIGNED). All new tables FK on this conceptually (only `edoc_user_labels.id` has actual FK to itself; no FK to `edoctitle` because that table may not have InnoDB FK setup — keep loose).
- `edoctitle.participant` is a **CSV string** of emails (legacy). `edoc_document_tags` is the normalized version. Use `edoc_document_tags` for new code.
- `edoctitle.doctype` is a single string (not normalized). Category tabs read DISTINCT from this column.
- All email fields are lowercased on insert — match style in existing models.

---

## SKILLS & TOOLS IN USE
- CodeIgniter 4 (PHP) — `\CodeIgniter\Model` base class with `$allowedFields`, `$useTimestamps`
- MySQL/MariaDB (utf8mb4_unicode_ci) — InnoDB
- Tailwind CSS (CDN) + Sarabun font + Font Awesome + Bootstrap 5 modals — existing convention in `showEdoc.php`
- DataTables 1.13.7 — existing; Phase B may keep or replace with custom virtual list
- jQuery — assumed present (used by existing edoc views)

---

## QUICK START
> Apply the schema first: `mysql -u root -p newscience < database/edoc_gmail_layout.sql`. Then start Phase B: create `app/Controllers/Edoc/EdocController::inbox()` action + `app/Views/edoc/documents/inbox.php` Gmail-style view. Reuse the visibility WHERE clause from `EdocController::getDoc()` at line ~299 (owner = userEmail OR participant LIKE %userEmail%) and LEFT JOIN `edoc_user_flags` + aggregate labels from `edoc_document_labels` for the logged-in user. Add route `edoc/inbox` → `EdocController::inbox`.

---

## MEMORY REFS
- (none specifically saved for this session — all context is in this handoff)
- User answered design choices: labels = **per-user**, forward visibility = **both** (mixed in inbox + dedicated "Forwarded" tab)
