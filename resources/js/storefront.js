import { esc, renderPacks } from './pack';

const { L, products, tg, tel, orderUrl, csrf } = window.SAXIY;
const $ = (selector) => document.querySelector(selector);

renderPacks();

/* ---------- cart in pieces (kept across pages); consumer min order and step per product ---------- */
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

const minQty = (id) => products[id]?.min || 1;
const stepQty = (id) => products[id]?.step || 1;
const fitQty = (id, qty) => (qty <= 0 ? 0 : qty <= minQty(id) ? minQty(id) : minQty(id) + Math.ceil((qty - minQty(id)) / stepQty(id)) * stepQty(id));
const nextQty = (id, qty) => (qty ? qty + stepQty(id) : minQty(id));
const prevQty = (id, qty) => (qty - stepQty(id) < minQty(id) ? 0 : qty - stepQty(id));
let orderNotice = '';

const describe = (id) => `${products[id].w} ${L.g}${products[id].box ? ` · ${products[id].box} ${L.pieces}` : ''}`;

function toast(message) {
    const el = $('#toast');
    el.textContent = message;
    el.toggleAttribute('data-on', true);
    clearTimeout(toast.timer);
    toast.timer = setTimeout(() => el.toggleAttribute('data-on', false), 1800);
}

function inquiryText(extra) {
    const lines = Object.entries(cart).map(([id, n]) => `• ${products[id].name} ${products[id].w} ${L.g} — ${n} ${L.pcs}`);
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
        : `<p class="py-6 ${orderNotice ? 'font-semibold text-leaf-dark' : 'text-muted'}">${esc(orderNotice || L.empty)}</p>`;
    const link = telegramLink(inquiryText());
    $('#dFoot').innerHTML = items.length
        ? `<form id="ck" class="grid gap-2.5" novalidate>
            <div><b class="block">${esc(L.checkoutH)}</b><span class="text-[13px] text-muted">${esc(L.checkoutP)}</span></div>
            <input class="field" id="ckName" name="name" autocomplete="name" placeholder="${esc(L.cName)}" required>
            <input class="field" id="ckPhone" name="phone" type="tel" autocomplete="tel" inputmode="tel" placeholder="${esc(L.cPhone)} +998 …" required>
            <input class="field" id="ckComment" name="comment" placeholder="${esc(L.cComment)}">
            <p id="ckErr" role="alert" class="text-sm text-red-700 empty:hidden"></p>
            <button class="btn btn-primary" id="ckSend">${esc(L.orderBtn)}</button>
          </form>
          <div class="flex flex-wrap gap-2">${link ? `<a class="btn btn-outline btn-sm" target="_blank" rel="noopener" href="${esc(link)}">${esc(L.sendTg)}</a>` : ''}${tel ? `<a class="btn btn-outline btn-sm" href="tel:${esc(tel.replace(/[^\d+]/g, ''))}">${esc(L.toCall)}</a>` : ''}<button class="btn btn-outline btn-sm" type="button" id="dClear">${esc(L.clear)}</button></div>`
        : '';
}

function setCart(id, qty) {
    if (!products[id]) return;
    if (qty > 0) cart[id] = qty;
    else delete cart[id];
    orderNotice = '';
    saveCart();
    renderCart();
}

const addCart = (id, pieces) => setCart(id, pieces ? fitQty(id, (cart[id] || 0) + pieces) : nextQty(id, cart[id] || 0));

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
    if (el.dataset.c) { setCart(el.dataset.c, Number(el.dataset.d) > 0 ? nextQty(el.dataset.c, cart[el.dataset.c] || 0) : prevQty(el.dataset.c, cart[el.dataset.c] || 0)); return; }
    if (el.dataset.w) {
        filterWeight = el.dataset.w;
        document.querySelectorAll('[data-w].chip').forEach((chip) => chip.setAttribute('aria-pressed', String(chip === el)));
        applyCatalogFilters();
        return;
    }
    if (el.dataset.q) { const id = $('#iadd').dataset.id; const v = $('#iqv'); v.textContent = Math.max(minQty(id), Number(v.textContent) + Number(el.dataset.q) * stepQty(id)); return; }
    if (el.id === 'iadd') { addCart(el.dataset.id, Number($('#iqv').textContent)); toast(L.added); return; }
    if (el.dataset.t) { document.querySelectorAll('#ct .chip').forEach((chip) => chip.setAttribute('aria-pressed', String(chip === el))); }
});
$('#scrim').addEventListener('click', () => openDrawer(false));
document.addEventListener('keydown', (e) => { if (e.key === 'Escape') openDrawer(false); });
document.addEventListener('change', (e) => { if (e.target.id === 'sortSel') applyCatalogFilters(); });
async function sendOrder(form) {
    const name = form.name.value.trim();
    const phone = form.phone.value.trim();
    const error = $('#ckErr');
    error.textContent = !name ? L.errName : !phone ? L.errPhone : '';
    if (error.textContent) return;
    const button = $('#ckSend');
    button.disabled = true;
    button.textContent = L.sending;
    try {
        const response = await fetch(orderUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ name, phone, comment: form.comment.value.trim(), items: Object.entries(cart).map(([slug, quantity]) => ({ slug, quantity })) }),
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(Object.values(data.errors || {})[0]?.[0] || L.orderFailed);
        Object.keys(cart).forEach((k) => delete cart[k]);
        saveCart();
        orderNotice = data.message;
        renderCart();
    } catch (err) {
        error.textContent = err.message || L.orderFailed;
        button.disabled = false;
        button.textContent = L.orderBtn;
    }
}

document.addEventListener('submit', (e) => {
    if (e.target.id === 'ck') { e.preventDefault(); sendOrder(e.target); return; }
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
