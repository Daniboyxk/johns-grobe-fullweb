<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

<!-- Tipografies Oficials de John's Grobe: Lora & Nunito Sans -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,400;1,600&family=Nunito+Sans:ital,wght@0,300;0,400;0,600;0,700;0,800;0,900;1,400;1,700&display=swap" rel="stylesheet">

<link href="./dist/output.css" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest"></script>

<style>
  :root {
    --font-heading: 'Lora', Georgia, serif;
    --font-body: 'Nunito Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    --color-burgundy: #D21F3C;
    --color-burgundy-dark: #8B1225;
    --color-gold: #C6A664;
    --color-bg-dark: #0f1013;
  }
  body {
    font-family: var(--font-body) !important;
    background-color: var(--color-bg-dark) !important;
    color: #F8F9FA !important;
  }
  h1, h2, h3, .font-heading {
    font-family: var(--font-heading);
  }
  .btn-primario {
    background: linear-gradient(135deg, #D21F3C 0%, #A81930 100%) !important;
    border: 1px solid rgba(210, 31, 60, 0.4) !important;
    box-shadow: 0 4px 15px rgba(210, 31, 60, 0.35) !important;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
  }
  .btn-primario:hover {
    box-shadow: 0 6px 22px rgba(210, 31, 60, 0.6) !important;
    transform: translateY(-2px) !important;
  }
  .card-grobe {
    background: rgba(26, 28, 35, 0.7) !important;
    backdrop-filter: blur(12px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 1.5rem !important;
  }
</style>
