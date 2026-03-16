# Update User Nicknames Command

## Description
CLI command to update the `nickname` field for users who don't have one, using their Thai name (tf_name + tl_name).

## Usage

### Dry Run (Preview changes without applying)
```bash
php spark update:nicknames --dry-run
```

### Update Users (Only those without nickname)
```bash
php spark update:nicknames
```

### Force Update (Overwrite existing nicknames)
```bash
php spark update:nicknames --force
```

## What it does

1. **Finds all users** in the user table
2. **Generates nickname** from Thai name (tf_name + tl_name)
3. **Updates** users who:
   - Have Thai name but no nickname (default mode)
   - Have Thai name (force mode - overwrites existing nickname)
4. **Skips** users who:
   - Don't have Thai name
   - Already have nickname (unless --force is used)

## Output Example

```
=== Update User Nicknames ===

Found 150 users in database

✓ Users to update: 45

Sample updates (first 10):
  [123] somchai
    Thai Name: "สมชาย ใจดี"
    Eng Name: "Somchai Jaidee"
    Nickname: (empty) → "สมชาย ใจดี"

  [124] siriwan
    Thai Name: "สิริวรรณ มีใจ"
    Eng Name: "Siriwan Meejai"
    Current Nickname: "siri" → "สิริวรรณ มีใจ"

... and 35 more users

⚠ Users skipped: 105
  - Already has nickname: 100 users
  - No Thai name (tf_name + tl_name): 5 users

=== END ===
```

## Safety Features

- **Dry run mode** to preview changes
- **Force mode** to overwrite existing nicknames
- **Error handling** for database issues
- **Detailed logging** of what was updated/skipped

## Running the Command

1. **Preview first** (recommended):
   ```bash
   php spark update:nicknames --dry-run
   ```

2. **Apply updates**:
   ```bash
   php spark update:nicknames
   ```

3. **Force overwrite** (if needed):
   ```bash
   php spark update:nicknames --force
   ```
