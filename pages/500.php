<?php
/**
 * 500 error page. Called by the global exception handler, which may fire
 * before the database or settings are available — so this page must be
 * completely self-contained and must never query the database.
 */
if (!headers_sent()) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Something went wrong</title>
<style>
  :root { color-scheme: dark; }
  body { margin:0; min-height:100vh; display:grid; place-items:center; background:#0b0d10; color:#eef1f5;
    font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,sans-serif; text-align:center; padding:2rem; }
  .box { max-width:520px; }
  h1 { font-size:clamp(3rem,10vw,5rem); margin:0; letter-spacing:-.03em; }
  h2 { font-size:1.3rem; font-weight:600; margin:.4rem 0 .6rem; }
  p { color:#9aa3af; line-height:1.6; }
  a { display:inline-block; margin-top:1.4rem; background:#5b8cff; color:#fff; text-decoration:none;
    padding:.75rem 1.4rem; border-radius:999px; font-weight:600; }
</style>
</head>
<body>
  <div class="box">
    <h1>500</h1>
    <h2>Something went wrong on our end.</h2>
    <p>We hit an unexpected error. Please try again in a moment — if it keeps happening, come back a little later.</p>
    <a href="/">Back to home</a>
  </div>
</body>
</html>
