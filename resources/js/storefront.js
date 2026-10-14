import { esc, renderPacks } from './pack';

const { L, products, tg, tel } = window.SAXIY;
const $ = (selector) => document.querySelector(selector);

renderPacks();

/* ---------- inquiry cart (kept across pages) ---------- */
const cart = (() => {
    try {
        const saved = JSON.parse(localStorage.getItem('saxiy-cart') || '{}');
        return Object.fromEntries(Object.entries(saved).filter(([id, n]) => products[id] && n > 0));
    } catch (e) {
        return {};
    }
})();

function saveCart() {
    try {
        localStorage.setItem('saxiy-cart', JSON.stringify(cart));
    } catch (e) {
        /* storage unavailable */
    }
}

const describe = (id) => `${products[id].w} ${L.g}${products[id].box ? ` · ${products[id].box} ${L.pieces}` : ''}`;

function toast(message) {
    const el = $('#toast');
    el.textContent = message;
    el.toggleAttribute('data-on', true);
    clearTimeout(toast.timer);
    toast.timer = setTimeout(() => el.toggleAttribute('data-on', false), 1800);
}

function inquiryText(extra) {
    const lines = Object.entries(cart).map(([id, n]) => `• ${products[id].name} ${products[id].w} ${L.g} — ${n} ${L.boxes}`);
    return [L.order, ...lines, extra || ''].filter(Boolean).join('\n');
}

/* t.me/<username>?text= opens the chat with the message pre-filled */
function telegramLink(text) {
    return tg ? `${tg}?text=${encodeURIComponent(text)}` : null;
}

function renderCart() {
    const items = Object.entries(cart);
    $('#cartN').textContent = Object.values(cart).reduce((a, b) => a + b, 0);
    $('#dList').innerHTML = items.length
        ? items.map(([id, q]) => `<div class="flex items-center justify-between gap-3 border-b border-line py-3.5">
            <div class="min-w-0"><b class="block">${esc(products[id].name)}</b><span class="text-[13px] text-muted">${esc(describe(id))}</span></div>
            <div class="inline-flex shrink-0 items-center rounded-full border-[1.5px] border-line">
              <button type="button" data-c="${esc(id)}" data-d="-1" aria-label="−" class="h-9 w-8 text-lg font-bold">−</button><span class="min-w-6 text-center font-bold">${q}</span><button type="button" data-c="${esc(id)}" data-d="1" aria-label="+" class="h-9 w-8 text-lg font-bold">+</button>
            </div></div>`).join('')
        : `<p class="py-6 text-muted">${esc(L.empty)}</p>`;
    const link = telegramLink(inquiryText());
    $('#dFoot').innerHTML = items.length
        ? `${link ? `<a class="btn btn-telegram" target="_blank" rel="noopener" href="${esc(link)}">${esc(L.sendTg)}</a>` : ''}${tel ? `<a class="btn btn-outline" href="tel:${esc(tel.replace(/[^\d+]/g, ''))}">${esc(L.toCall)}</a>` : ''}<button class="btn btn-outline btn-sm" type="button" id="dClear">${esc(L.clear)}</button>`
        : '';
}

function addCart(id, n = 1) {
    if (!products[id]) return;
    cart[id] = (cart[id] || 0) + n;
    if (cart[id] <= 0) delete cart[id];
    saveCart();
    renderCart();
}

function openDrawer(open) {
    $('#drawer').toggleAttribute('data-open', open);
    $('#scrim').toggleAttribute('data-open', open);
    $('#drawer').setAttribute('aria-hidden', String(!open));
    if (open) $('#dClose').focus();
}

/* ---------- catalog filters ---------- */
let filterWeight = 'all';

function applyCatalogFilters() {
    const grid = $('#grid');
    if (!grid) return;
    const cards = [...grid.querySelectorAll('.card')];
    cards.forEach((card) => { card.hidden = filterWeight !== 'all' && card.dataset.w !== filterWeight; });
    const sort = $('#sortSel')?.value || 'def';
    if (!grid.dataset.order) cards.forEach((card, i) => { card.dataset.i = i; });
    grid.dataset.order = '1';
    cards
        .sort((a, b) => (sort === 'w' ? a.dataset.w - b.dataset.w : sort === 'wd' ? b.dataset.w - a.dataset.w : a.dataset.i - b.dataset.i))
        .forEach((card) => grid.appendChild(card));
}

/* ---------- events ---------- */
document.addEventListener('click', (e) => {
    const el = e.target.closest('button');
    if (!el) return;
    if (el.dataset.add) { addCart(el.dataset.add); toast(L.added); return; }
    if (el.dataset.open || el.id === 'cartBtn') { openDrawer(true); return; }
    if (el.id === 'dClose') { openDrawer(false); return; }
    if (el.id === 'dClear') { Object.keys(cart).forEach((k) => delete cart[k]); saveCart(); renderCart(); return; }
    if (el.dataset.c) { addCart(el.dataset.c, Number(el.dataset.d)); return; }
    if (el.dataset.w) {
        filterWeight = el.dataset.w;
        document.querySelectorAll('[data-w].chip').forEach((chip) => chip.setAttribute('aria-pressed', String(chip === el)));
        applyCatalogFilters();
        return;
    }
    if (el.dataset.q) { const v = $('#iqv'); v.textContent = Math.max(1, Number(v.textContent) + Number(el.dataset.q)); return; }
    if (el.id === 'iadd') { addCart(el.dataset.id, Number($('#iqv').textContent)); toast(L.added); return; }
    if (el.dataset.t) { document.querySelectorAll('#ct .chip').forEach((chip) => chip.setAttribute('aria-pressed', String(chip === el))); }
});
$('#scrim').addEventListener('click', () => openDrawer(false));
document.addEventListener('keydown', (e) => { if (e.key === 'Escape') openDrawer(false); });
document.addEventListener('change', (e) => { if (e.target.id === 'sortSel') applyCatalogFilters(); });
document.addEventListener('submit', (e) => {
    if (e.target.id !== 'cf') return;
    e.preventDefault();
    const name = $('#cn').value.trim();
    const phone = $('#cp').value.trim();
    $('#e1').textContent = name ? '' : L.errName;
    $('#e2').textContent = phone ? '' : L.errPhone;
    if (!name || !phone) return;
    const type = document.querySelector('#ct .chip[aria-pressed="true"]')?.dataset.t || '';
    const company = $('#cc').value.trim();
    const info = `${L.cName}: ${name}\n${L.cPhone}: ${phone}\n${company ? `${L.cCompany}: ${company}\n` : ''}${type}\n${$('#cm').value}`;
    const link = telegramLink(Object.keys(cart).length ? inquiryText(info) : `${L.hi}\n${info}`);
    if (link) window.open(link, '_blank', 'noopener');
});

renderCart();
