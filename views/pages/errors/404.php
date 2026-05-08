<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>404 — Page Not Found | Codentra</title>
<style>
  :root {
    --clr-bg: #0A1C28;
    --clr-accent: #2A9D8F;
    --clr-text: #FFFFFF;
    --clr-muted: rgba(255,255,255,0.65);
    --clr-cta: #F4A261;
  }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    background: var(--clr-bg);
    color: var(--clr-text);
    font-family: system-ui, sans-serif;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    text-align: center;
    padding: 2rem;
  }
  h1 {
    font-size: clamp(4rem, 15vw, 8rem);
    color: var(--clr-accent);
    line-height: 1;
    margin-bottom: 1rem;
  }
  p { color: var(--clr-muted); font-size: 1.125rem; margin-bottom: 2rem; }
  a {
    display: inline-block;
    padding: 0.75rem 2rem;
    background: var(--clr-accent);
    color: #fff;
    text-decoration: none;
    border-radius: 0.75rem;
    font-weight: 600;
    transition: opacity 0.2s;
  }
  a:hover { opacity: 0.85; }
</style>
</head>
<body>
  <h1>404</h1>
  <p>This page doesn't exist — yet.</p>
  <a href="/">Back to Home</a>
</body>
</html>
