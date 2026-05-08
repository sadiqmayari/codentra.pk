# Codentra — Setup Files

These three files go in the **root of your repo**. They are your control panel for the entire build.

| File | Purpose | When you read it |
|------|---------|------------------|
| `CLAUDE.md` | Full project spec — brand, colors, structure, DB, requirements | Claude Code auto-reads on every session. You rarely open it. |
| `BUILD_PROMPTS.md` | 11 ordered prompts to copy-paste into Claude Code | Each time you start a new build phase |
| `DEPLOYMENT.md` | Hostinger setup + ongoing deploy workflow | Once at start, then for troubleshooting |

---

## 🚀 Your Workflow (Step by Step)

### Today (one-time setup, ~15 min)
1. Drop `CLAUDE.md`, `BUILD_PROMPTS.md`, `DEPLOYMENT.md` into your repo root.
2. Follow `DEPLOYMENT.md` sections 1–5 (Hostinger config: PHP version, MySQL DB, SSL, Git connect).
3. Commit and push these three files. Verify Hostinger pulled them.

### Then (build the site, ~11 sessions)
1. Open Claude Code in your repo.
2. Open `BUILD_PROMPTS.md`, copy **Phase 1**, paste into Claude Code.
3. Let it work. Review the diff. Commit. Push.
4. Hostinger auto-deploys. Test on live URL.
5. Move to next phase. Repeat.

### Forever (maintenance)
- Need a tweak? Use the maintenance prompts at the bottom of `BUILD_PROMPTS.md`.
- Always start prompts with: **"Read CLAUDE.md."** — saves tokens.

---

## 💰 Why This Saves Tokens

❌ **Without this setup**, every Claude Code session starts with:
> "Build me a contact page. The brand is Codentra, colors are #0A1C28..., font is Ubuntu, the form should have these fields..., it needs to save to MySQL with these columns..."
>
> ~800 tokens of context, every time.

✅ **With this setup**:
> "Read CLAUDE.md. Build the contact page per spec."
>
> ~12 tokens. Spec loaded once. Context preserved across sessions.

Across an 11-phase build + ongoing maintenance, this saves **tens of thousands of tokens**.

---

## 🎯 Critical Rules

1. **Never modify `CLAUDE.md` mid-build without intent.** It's your single source of truth.
2. **One phase per session.** Don't combine. Smaller context = better output + fewer tokens.
3. **Test locally OR after each push.** Catching bugs in Phase 3 is cheaper than debugging Phase 8.
4. **Commit after each phase.** Easy rollback if something breaks.

---

## ❓ FAQ

**Q: Can I change the design later?**
A: Yes. Edit colors/fonts in `CLAUDE.md`'s Design System section, then prompt Claude Code: *"Read CLAUDE.md. The design tokens changed — update public/css/style.css and any inline styles to match."*

**Q: What if I want a feature not in the spec?**
A: Add it to `CLAUDE.md` first (so future sessions know it exists), then prompt Claude Code to build it.

**Q: How do I add team members or scale up the codebase?**
A: The MVC structure in `CLAUDE.md` is built for it. Add new controllers/models/views following the same patterns.

**Q: I'm getting hit with bots/spam on the contact form.**
A: Phase 5 already adds rate limiting. If it's still bad: prompt Claude Code to add a honeypot field and/or hCaptcha integration.
