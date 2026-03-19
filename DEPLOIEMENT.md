# SoccerMidable.ca — v3 Turnkey Deployment Guide

## What's New in v3

### Feedback Applied
- **Colors & Feel**: Warm, kid-friendly palette (no more dark/crypto vibe). Bright creams, soft purples, gold accents
- **Hamburger Menu**: Large, colored purple button — very visible on mobile
- **Less Scrolling**: Sections are more compact with tighter padding
- **Pictures Aligned**: Consistent aspect ratios, proper grid alignment, professional fit
- **Partner Logos**: Miniature side-by-side in a compact strip
- **No Right Gap**: Container padding and grids fixed for edge-to-edge content
- **Jay Yogo Yemo**: Added to team as Directeur des partenariats stratégiques
- **SoccerMidable Kid Style**: Brand name uses Baloo 2 (playful cursive) with Soccer in purple + Midable in gold throughout entire site
- **Responsive**: Completely rebuilt breakpoints — 480px, 768px, 1100px
- **Inspired by**: soccershots.com / kidsunited.com style (bright, parent-friendly, round buttons, warm colors)

### Design System
- **Fonts**: Baloo 2 (display/brand), Nunito (headings/UI), Quicksand (body)
- **Colors**: Purple #6C3FA0, Gold #F5A623, Cream #FFFDF7, Lavender #F3EDFF
- **Buttons**: Rounded (50px radius), pill-shaped — kid-friendly feel
- **Sections**: Alternating white/cream/lavender backgrounds for visual rhythm

---

## Deployment

### Static Site (Recommended)
Simply upload the `sm_final/` folder to any web host:

```
sm_final/
├── index.html
├── css/style.css
├── js/main.js
└── images/
    ├── hero.jpg
    ├── logo-purple.png
    ├── logo-white.png
    ├── kids/ (6 photos)
    └── partners/ (5 logos)
```

### Options
| Host | How |
|------|-----|
| **Netlify** | Drag & drop `sm_final/` folder |
| **Vercel** | Import repo or drag & drop |
| **GitHub Pages** | Push to repo, enable Pages |
| **cPanel / FTP** | Upload contents of `sm_final/` to `public_html/` |
| **Cloudflare Pages** | Connect repo or direct upload |

### Custom Domain (soccermidable.ca)
1. Point DNS A record to host IP (or CNAME to host URL)
2. Enable SSL/HTTPS (most hosts do this automatically)
3. Update `og:url` and `canonical` in `index.html` if needed

### Form Backend
The registration form defaults to `mailto:` fallback. To enable Formspree:
1. Create account at formspree.io
2. Get your form ID
3. Replace `YOUR_FORM_ID` in `js/main.js` line 93

---

## Team (Current)
| Name | Role |
|------|------|
| Marc-Cyrille Kamdem | Co-Fondateur |
| Jessica Efole | Co-Fondatrice |
| Grace Obiang | Dir. des opérations |
| Djeinaba Kane | Dir. des programmes |
| Alexandrine Kamdem | Dir. des finances |
| **Jay Yogo Yemo** | **Dir. des partenariats stratégiques** |

---

## File Sizes
- HTML: ~27 KB
- CSS: ~24 KB  
- JS: ~5 KB
- Images: ~1.4 MB
- **Total: ~1.5 MB**

---

*SoccerMidable.ca v3 — February 2026*
