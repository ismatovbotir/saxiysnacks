/**
 * SVG packaging illustrations used when a product has no photo.
 * p = { art, v, band, w, name } — art/v come from App\Enums\ProductIllustration ("art:variant").
 */
const esc = (value) => String(value).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);

const NUT_COLORS = { peanut: '#E5C27A', cashew: '#EBD3A0', almond: '#8B4A24', pista: '#C9BE85', pit: '#A5623A' };

function contents(p) {
    let s = '';
    if (p.art === 'ball' || p.art === 'ball1') {
        const pos = p.art === 'ball1' ? [[100, 120]] : [[68, 88], [132, 88], [68, 140], [132, 140], [68, 192], [132, 192]];
        pos.forEach(([x, y], i) => {
            let f = '#F4EEDD';
            if (p.v === 'mix') f = i < 2 ? '#EFE2E6' : i < 4 ? '#F6F1E3' : '#EBB99B';
            const r = p.art === 'ball1' ? 38 : 26;
            s += `<circle cx="${x}" cy="${y}" r="${r}" fill="${f}" stroke="#D8CFB8" stroke-width="1.5"/>`;
            if (p.v === 'berry' || (p.v === 'mix' && i < 2)) {
                for (let k = 0; k < 7; k++) {
                    const a = k * 2.3 + i, d = (k % 3 + 1) * 7;
                    s += `<circle cx="${x + Math.cos(a) * d}" cy="${y + Math.sin(a) * d}" r="2.6" fill="#5B2A63"/>`;
                }
            }
            if (p.v === 'mix' && i >= 4) {
                for (let k = 0; k < 6; k++) s += `<circle cx="${x + Math.cos(k * 2) * 11}" cy="${y + Math.sin(k * 2) * 11}" r="2" fill="#D98A66"/>`;
            }
            s += `<ellipse cx="${x - r * 0.3}" cy="${y - r * 0.4}" rx="${r * 0.35}" ry="${r * 0.2}" fill="#fff" opacity=".55"/>`;
        });
    }
    if (p.art === 'square') {
        [[56, 72], [104, 72], [56, 124], [104, 124], [56, 176], [104, 176]].forEach(([x, y]) => {
            s += `<rect x="${x}" y="${y}" width="44" height="46" rx="4" fill="#F2EBD6" stroke="#D8CFB8"/>`;
            for (let k = 0; k < 9; k++) s += `<circle cx="${x + 6 + (k * 13) % 34}" cy="${y + 8 + (k * 7) % 32}" r="1.6" fill="#DAD0B4"/>`;
        });
    }
    if (p.art === 'nut') {
        const c = NUT_COLORS[p.v] || '#E5C27A';
        for (let i = 0; i < 11; i++) {
            const x = 52 + (i % 3) * 48 + (Math.floor(i / 3) % 2) * 12, y = 80 + Math.floor(i / 3) * 38;
            const rot = (i * 37) % 80 - 40;
            if (p.v === 'cashew') s += `<path transform="rotate(${rot} ${x} ${y})" d="M${x - 16} ${y + 2}c2-14 22-16 30-4 3 5-2 9-6 6-6-6-14-4-15 6-1 5-9 5-9-8Z" fill="${c}" stroke="#B99753" stroke-width="1.2"/>`;
            else if (p.v === 'pista') s += `<g transform="rotate(${rot} ${x} ${y})"><ellipse cx="${x}" cy="${y}" rx="15" ry="10" fill="${c}" stroke="#8E8348" stroke-width="1.2"/><ellipse cx="${x + 3}" cy="${y}" rx="8" ry="6" fill="#9DB35B"/></g>`;
            else s += `<ellipse transform="rotate(${rot} ${x} ${y})" cx="${x}" cy="${y}" rx="${p.v === 'peanut' ? 13 : 15}" ry="${p.v === 'peanut' ? 9 : 10}" fill="${c}" stroke="rgba(0,0,0,.25)" stroke-width="1.2"/>`;
        }
    }
    if (p.art === 'stick') {
        for (let i = 0; i < 8; i++) {
            const x = 48 + i * 13;
            s += `<rect x="${x}" y="${62 + (i % 2) * 5}" width="10" height="${150 - (i % 3) * 8}" rx="5" fill="${i % 3 === 0 ? '#E9C777' : '#DDAE4A'}" stroke="#B8862C" stroke-width="1"/>`;
        }
    }
    if (p.art === 'stick1') {
        s += '<rect x="78" y="56" width="44" height="170" rx="10" fill="#F6F3E8" stroke="#D9D2BC"/><text x="100" y="140" transform="rotate(-90 100 140)" text-anchor="middle" font-family="Georgia,serif" font-weight="700" font-size="16" fill="#5D5B4E" letter-spacing="2">TOSH QURT</text>';
    }
    return s;
}

export function pack(p) {
    const label = p.art === 'nut' ? p.name.toUpperCase() : p.art === 'stick' ? 'SULUGUNI' : (p.art === 'ball' || p.art === 'square') ? 'BIOKURT' : 'KURT';
    const dark = p.art === 'stick';
    return `<svg viewBox="0 0 200 280" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
  <ellipse cx="100" cy="270" rx="70" ry="6" fill="#000" opacity=".12"/>
  <rect x="20" y="6" width="160" height="258" rx="8" fill="${dark ? '#2A2A2A' : '#fff'}" stroke="rgba(0,0,0,.12)"/>
  <path d="M20 6h160v50c-30 16-100 16-160 0Z" fill="${p.band}"/>
  <path d="M84 30a16 16 0 1 0 12 26 14 14 0 0 1-12-26Z" fill="#F59B00"/><path d="M78 38c6-6 14-7 22-1-7-1-16 0-22 1Z" fill="#2FA354"/>
  <text x="116" y="42" font-family="Times New Roman,serif" font-weight="700" font-size="17" fill="#fff" text-anchor="middle" style="paint-order:stroke" stroke="rgba(0,0,0,.25)" stroke-width="1">SAXIY</text>
  <text x="100" y="72" text-anchor="middle" font-family="Arial,sans-serif" font-weight="800" font-size="9" letter-spacing="1" fill="${dark ? '#F2A900' : p.band}">${esc(label)}</text>
  <rect x="32" y="80" width="136" height="150" rx="6" fill="${dark ? '#3B3B3B' : '#EEF0EA'}"/>
  ${contents(p)}
  <rect x="20" y="236" width="160" height="28" fill="${p.band}"/>
  <text x="100" y="255" text-anchor="middle" font-family="Arial,sans-serif" font-weight="800" font-size="14" fill="#fff">${esc(p.w)} g</text>
</svg>`;
}

export function renderPacks(root = document) {
    root.querySelectorAll('[data-pack]').forEach((el) => {
        try {
            el.innerHTML = pack(JSON.parse(el.dataset.pack));
        } catch (e) {
            /* leave the placeholder empty */
        }
    });
}

export { esc };
