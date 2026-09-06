# Secrets vault

Console → **Secrets vault** (`/console/vault`). Stores logins and credentials
(title, username, password, URL, notes, 2FA seed, arbitrary custom fields) in
folders. Replaces keeping client logins in a standalone KeePass file.

## How it protects things

- Secret fields (`password`, `notes`, `totp_secret`, custom fields marked
  *secret*) are **encrypted at rest** with the app key (`encrypted` cast). The
  DB never holds them in clear.
- Viewing a secret requires **re-confirming your account password**. That
  unlocks the vault for `VAULT_UNLOCK_TTL` seconds (default 300); after that it
  re-locks. Editing an entry needs the same unlock.
- **Every reveal, edit and export is written to the activity log** (event
  `revealed` / `exported`), visible on the entry's *Access log* and in
  Administration → Activity log.
- `vault.*` permissions gate the module and are **excluded from API token
  abilities** — the vault is never reachable over `/api`.

## Exports

Both live under the **Export** menu (require the vault unlocked):

| Format | How to open |
| --- | --- |
| **KeePass 2 XML** | KeePass 2 / KeePassXC → *File → Import → "KeePass 2 XML (2.x)"*. Unencrypted file — download, import, delete. Full fidelity (groups, all fields, 2FA). |
| **KeePass database (.kdbx)** | Double-click; opens in KeePass2 / KeePassXC / KeePassium. Encrypted with a password you set at download time. Needs the Python helper below. |

### Enabling the .kdbx export

The `.kdbx` writer runs a small Python script (`scripts/vault_to_kdbx.py`) that
uses [`pykeepass`](https://github.com/libkeepass/pykeepass). Provision a venv
and point the app at it:

```bash
# on the server, in the site root
python3 -m venv storage/app/.venv
storage/app/.venv/bin/pip install pykeepass
```

```dotenv
VAULT_KDBX_PYTHON=/home/forge/africs.gm/storage/app/.venv/bin/python
VAULT_UNLOCK_TTL=300
```

Leave `VAULT_KDBX_PYTHON` empty and the `.kdbx` option is disabled — the XML
export still works with no dependencies.
