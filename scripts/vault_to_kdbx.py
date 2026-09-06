#!/usr/bin/env python3
"""
Build a KeePass .kdbx from the Africs vault.

    python3 vault_to_kdbx.py <output_path> <password>   < payload.json

payload.json:
  {"name": "...", "groups": [{"name": "...", "entries": [
      {"title","username","password","url","notes","otp","custom":[{label,value,secret}]}
  ]}]}

Requires: pykeepass  (pip install pykeepass)
"""
import json
import sys

from pykeepass import create_database


def main() -> int:
    if len(sys.argv) != 3:
        print("usage: vault_to_kdbx.py <output_path> <password>", file=sys.stderr)
        return 2

    out_path, password = sys.argv[1], sys.argv[2]
    payload = json.load(sys.stdin)

    kp = create_database(out_path, password=password)

    for group in payload.get("groups", []):
        g = kp.add_group(kp.root_group, group.get("name") or "Group")

        for entry in group.get("entries", []):
            e = kp.add_entry(
                g,
                title=entry.get("title") or "",
                username=entry.get("username") or "",
                password=entry.get("password") or "",
                url=entry.get("url") or "",
                notes=entry.get("notes") or "",
            )
            if entry.get("otp"):
                try:
                    e.otp = entry["otp"]
                except Exception:
                    e.set_custom_property("TOTP Secret", entry["otp"], protect=True)
            for field in entry.get("custom", []):
                label = field.get("label")
                if not label or label in ("Title", "UserName", "Password", "URL", "Notes", "otp"):
                    continue
                e.set_custom_property(label, field.get("value") or "", protect=bool(field.get("secret")))

    kp.save()
    return 0


if __name__ == "__main__":
    sys.exit(main())
