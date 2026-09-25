# Bibliothèques vendorisées

Fichiers copiés tels quels depuis les paquets npm officiels (aucune
modification), pour respecter CLAUDE.md §7 ("self-host + minifier/
concaténer les JS (pas de multi-CDN)"). Ne pas éditer ces fichiers à la
main.

| Fichier | Paquet npm | Version |
|---|---|---|
| `gsap.min.js` | `gsap` | 3.15.0 |
| `ScrollTrigger.min.js` | `gsap` (plugin inclus) | 3.15.0 |
| `SplitText.min.js` | `gsap` (plugin inclus, gratuit depuis 2025 — voir CLAUDE.md §2) | 3.15.0 |
| `lenis.min.js` | `lenis` | 1.3.26 |

## Mettre à jour

```bash
mkdir -p /tmp/gsap-update && cd /tmp/gsap-update
npm init -y
npm install gsap@3 lenis@1
cp node_modules/gsap/dist/gsap.min.js           <plugin>/assets/js/vendor/
cp node_modules/gsap/dist/ScrollTrigger.min.js  <plugin>/assets/js/vendor/
cp node_modules/gsap/dist/SplitText.min.js      <plugin>/assets/js/vendor/
cp node_modules/lenis/dist/lenis.min.js         <plugin>/assets/js/vendor/
```

Puis mettre à jour ce tableau et bumper `UP2A_CORE_VERSION` dans
`up2a-core.php` (le numéro de version du plugin sert de `?ver=` de
cache-busting pour tous les assets, y compris ces fichiers vendor).
