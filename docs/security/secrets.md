# Team secrets with SOPS + age

This template supports storing shared dev secrets in git using SOPS-encrypted
files. Plain .env files stay local and never get committed.

## Requirements

- sops: https://github.com/getsops/sops
- age: https://github.com/FiloSottile/age

## Setup (one time per team)

1) Each developer generates an age keypair:

```bash
age-keygen -o ~/.config/sops/age/keys.txt
```

2) Collect the public keys (lines starting with `public key:`) and add them to
`.sops.yaml`:

- Copy `.sops.yaml.example` to `.sops.yaml`
- Replace `AGE_PUBLIC_KEY_*` with your team's public keys
- Commit `.sops.yaml` to the repo

3) Create encrypted env files in `secrets/` (shared dev secrets):

```bash
make secrets-encrypt
```

This reads `.env`, `backend/.env`, and `frontend/.env` and writes:

- `secrets/.env.enc`
- `secrets/backend.env.enc`
- `secrets/frontend.env.enc`

Commit only the `*.enc` files.
If any of the source `.env` files are missing, they are skipped.

## Day-to-day usage

Decrypt the shared env files into local `.env` files:

```bash
make secrets-decrypt
```

To edit secrets, use SOPS directly:

```bash
sops secrets/.env.enc
sops secrets/backend.env.enc
sops secrets/frontend.env.enc
```

If an encrypted file is missing, it is skipped during decrypt.

## Good practices

- Never commit `.env` files or production secrets.
- Use least-privilege credentials for dev.
- Rotate keys and secrets when a team member leaves.
- CI should use the provider's secret store instead of `.env` files.
