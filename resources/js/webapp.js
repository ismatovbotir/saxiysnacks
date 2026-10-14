import { esc, renderPacks } from './pack';

const tg = window.Telegram?.WebApp;
const app = document.getElementById('app');
const tabsEl = document.getElementById('tabs');
const API = app.dataset.api;

const T = {
    uz: {
        catalog: 'Katalog', cart: 'Savat', orders: 'Buyurtmalar', profile: 'Profil', all: 'Hammasi', g: 'g', pcs: 'dona',
        openInTelegram: 'Iltimos, ilovani SAXIY Telegram botidan oching.',
        guestH: 'Narxlarni ko\'rish uchun ro\'yxatdan o\'ting', guestP: 'Bir daqiqa: ism va telefon — shundan so\'ng narxlar va buyurtma ochiladi.', register: 'Ro\'yxatdan o\'tish', editProfile: 'Ma\'lumotlarni o\'zgartirish',
        blockedH: 'Hisobingiz bloklangan', blockedP: 'Batafsil ma\'lumot uchun biz bilan bog\'laning. Katalogni ko\'rishingiz mumkin.',
        resellerBadge: '⭐ Diler narxlari', optional: 'ixtiyoriy',
        firstName: 'Ism', lastName: 'Familiya', phone: 'Telefon raqam', company: 'Kompaniya nomi', vat: 'STIR (INN)', submit: 'Yuborish', back: 'Orqaga',
        add: 'Savatga', perBox: 'quti', unavailable: 'Narx so\'rov bo\'yicha',
        cartEmpty: 'Savat bo\'sh. Katalogdan mahsulot qo\'shing.', comment: 'Izoh (yetkazish vaqti va h.k.)', total: 'Jami', placeOrder: 'Buyurtma berish',
        orderSent: 'Buyurtma yuborildi! Menejer tez orada tasdiqlaydi.', ordersEmpty: 'Hali buyurtmalar yo\'q.', error: 'Xatolik yuz berdi. Qayta urinib ko\'ring.',
        location: 'Yetkazish manzili', shareLocation: '📍 Joriy joylashuvni yuborish', updateLocation: '📍 Joylashuvni yangilash', locating: 'Joylashuv aniqlanmoqda…',
        locationReceived: 'Joylashuv qabul qilindi', useSaved: 'Saqlangan manzil', deliveryH: 'Bu buyurtmani qayerga yetkazamiz?',
        deliverySaved: 'Saqlangan manzilingizga yetkazamiz', changeInProfile: 'Profilda o\'zgartirish', viewOnMap: 'Xaritada ko\'rish',
        addressDetails: 'Manzil tafsilotlari (uy, podyezd, mo\'ljal)', chooseLocation: 'Yetkazish manzilini tanlang',
        locationFailed: 'Joylashuvni olib bo\'lmadi. Ruxsat bering yoki uni bot chatida yuboring: 📎 → Joylashuv.', openSettings: 'Sozlamalarni ochish',
        status: { new: 'Yangi', confirmed: 'Tasdiqlangan', shipped: 'Jo\'natilgan', completed: 'Bajarilgan', cancelled: 'Bekor qilingan' },
        profileStatus: { active: 'Faol', blocked: 'Bloklangan' }, profileType: { consumer: 'Chakana narxlar', reseller: 'Diler narxlari' }, prices: 'Narxlar',
        myList: '⭐ Sevimlilar', saveToList: 'Sevimlilarga qo\'shish', removeFromList: 'Sevimlilardan olib tashlash', addAllToCart: 'Hammasini savatga qo\'shish',
        saveCartToList: '⭐ Savatni sevimlilarga saqlash', listSaved: 'Sevimlilarga saqlandi', listEmpty: 'Sevimlilar hozircha bo\'sh. Keyingi buyurtmalar uchun mahsulotdagi ☆ tugmasini bosing.', usualQty: 'odatda', repeatOrder: '🔁 Buyurtmani takrorlash', addedToCart: 'Savatga qo\'shildi',
    },
    ru: {
        catalog: 'Каталог', cart: 'Корзина', orders: 'Заказы', profile: 'Профиль', all: 'Все', g: 'г', pcs: 'шт.',
        openInTelegram: 'Откройте приложение из Telegram-бота SAXIY.',
        guestH: 'Зарегистрируйтесь, чтобы видеть цены', guestP: 'Это минута: имя и телефон — и сразу откроются цены и заказ.', register: 'Зарегистрироваться', editProfile: 'Изменить данные',
        blockedH: 'Аккаунт заблокирован', blockedP: 'Свяжитесь с нами для подробностей. Каталог по-прежнему доступен.',
        resellerBadge: '⭐ Цены для реселлеров', optional: 'необязательно',
        firstName: 'Имя', lastName: 'Фамилия', phone: 'Номер телефона', company: 'Название компании', vat: 'ИНН', submit: 'Отправить', back: 'Назад',
        add: 'В корзину', perBox: 'кор.', unavailable: 'Цена по запросу',
        cartEmpty: 'Корзина пуста. Добавьте товары из каталога.', comment: 'Комментарий (время доставки и т.п.)', total: 'Итого', placeOrder: 'Оформить заказ',
        orderSent: 'Заказ отправлен! Менеджер скоро его подтвердит.', ordersEmpty: 'Заказов пока нет.', error: 'Что-то пошло не так. Попробуйте ещё раз.',
        location: 'Адрес доставки', shareLocation: '📍 Отправить текущую локацию', updateLocation: '📍 Обновить локацию', locating: 'Определяем локацию…',
        locationReceived: 'Локация получена', useSaved: 'Сохранённый адрес', deliveryH: 'Куда доставить этот заказ?',
        deliverySaved: 'Доставим на ваш сохранённый адрес', changeInProfile: 'Изменить в профиле', viewOnMap: 'Открыть на карте',
        addressDetails: 'Уточнение адреса (дом, подъезд, ориентир)', chooseLocation: 'Выберите адрес доставки',
        locationFailed: 'Не удалось получить локацию. Разрешите доступ или отправьте её в чат с ботом: 📎 → Геопозиция.', openSettings: 'Открыть настройки',
        status: { new: 'Новый', confirmed: 'Подтверждён', shipped: 'Отправлен', completed: 'Выполнен', cancelled: 'Отменён' },
        profileStatus: { active: 'Активен', blocked: 'Заблокирован' }, profileType: { consumer: 'Розничные цены', reseller: 'Цены для реселлеров' }, prices: 'Цены',
        myList: '⭐ Избранное', saveToList: 'Добавить в избранное', removeFromList: 'Убрать из избранного', addAllToCart: 'Добавить всё в корзину',
        saveCartToList: '⭐ Сохранить корзину в избранное', listSaved: 'Сохранено в избранное', listEmpty: 'В избранном пока пусто. Нажмите ☆ на товаре, чтобы быстро заказывать его в следующий раз.', usualQty: 'обычно', repeatOrder: '🔁 Повторить заказ', addedToCart: 'Добавлено в корзину',
    },
    en: {
        catalog: 'Catalog', cart: 'Cart', orders: 'Orders', profile: 'Profile', all: 'All', g: 'g', pcs: 'pcs',
        openInTelegram: 'Please open this app from the SAXIY Telegram bot.',
        guestH: 'Register to see prices', guestP: 'It takes a minute: your name and phone — then prices and ordering unlock right away.', register: 'Register', editProfile: 'Edit details',
        blockedH: 'Your account is blocked', blockedP: 'Please contact us for details. You can still browse the catalog.',
        resellerBadge: '⭐ Reseller prices', optional: 'optional',
        firstName: 'First name', lastName: 'Last name', phone: 'Phone number', company: 'Company name', vat: 'VAT / TIN', submit: 'Submit', back: 'Back',
        add: 'Add', perBox: 'box', unavailable: 'Price on request',
        cartEmpty: 'Your cart is empty. Add products from the catalog.', comment: 'Comment (delivery time, etc.)', total: 'Total', placeOrder: 'Place order',
        orderSent: 'Order sent! A manager will confirm it shortly.', ordersEmpty: 'No orders yet.', error: 'Something went wrong. Please try again.',
        location: 'Delivery location', shareLocation: '📍 Send my current location', updateLocation: '📍 Update location', locating: 'Getting your location…',
        locationReceived: 'Location received', useSaved: 'Saved location', deliveryH: 'Where should we deliver this order?',
        deliverySaved: 'We deliver to your saved location', changeInProfile: 'Change in profile', viewOnMap: 'View on map',
        addressDetails: 'Address details (house, entrance, landmark)', chooseLocation: 'Choose a delivery location',
        locationFailed: 'Could not get your location. Allow access, or send it in the bot chat: 📎 → Location.', openSettings: 'Open settings',
        status: { new: 'New', confirmed: 'Confirmed', shipped: 'Shipped', completed: 'Completed', cancelled: 'Cancelled' },
        profileStatus: { active: 'Active', blocked: 'Blocked' }, profileType: { consumer: 'Consumer prices', reseller: 'Reseller prices' }, prices: 'Prices',
        myList: '⭐ Favorites', saveToList: 'Add to favorites', removeFromList: 'Remove from favorites', addAllToCart: 'Add all to cart',
        saveCartToList: '⭐ Save cart to favorites', listSaved: 'Saved to favorites', listEmpty: 'No favorites yet. Tap ☆ on a product to reorder it quickly next time.', usualQty: 'usually', repeatOrder: '🔁 Repeat order', addedToCart: 'Added to cart',
    },
};

const tgUser = tg?.initDataUnsafe?.user || {};
const lang = ['uz', 'ru', 'en'].includes((tgUser.language_code || '').slice(0, 2)) ? tgUser.language_code.slice(0, 2) : 'ru';
const L = T[lang];
document.documentElement.lang = lang;

const state = {
    profile: null, categories: [], products: [], orders: [], saved: {}, notice: '', category: 'all', tab: 'catalog', view: null, cart: {}, comment: '', errors: {},
    /* location picked in the current form: { latitude, longitude } | 'saved' | null */
    pickedLocation: null, address: '', locating: false, locationError: false,
};

/* ---------- shared class lists (mobile-first) ---------- */
const ui = {
    btn: 'w-full rounded-xl bg-tg-button px-4 py-3 text-center font-bold text-tg-button-text active:opacity-80 disabled:opacity-50',
    btnGhost: 'w-full rounded-xl border border-tg-text/15 px-4 py-3 text-center font-semibold active:bg-tg-text/5',
    card: 'rounded-2xl bg-tg-surface',
    input: 'w-full rounded-xl border border-tg-text/15 bg-tg-surface px-3 py-3 text-base outline-none focus:border-tg-button focus:ring-2 focus:ring-tg-button/30',
    label: 'grid gap-1 text-sm font-semibold',
    hint: 'text-xs text-tg-hint',
    error: 'text-xs font-medium text-tg-danger',
    h2: 'mb-3 text-lg font-bold',
};

const money = (amount) => `${Number(amount).toLocaleString('ru-RU').replace(/,/g, ' ')} so'm`;
const name = (translations) => translations?.[lang] || translations?.ru || Object.values(translations || {}).find(Boolean) || '';
const isActive = () => state.profile?.status === 'active';
const isReseller = () => isActive() && state.profile?.type === 'reseller';
const savedLocation = () => {
    const c = state.profile?.client;
    return c && c.latitude !== null && c.longitude !== null ? { latitude: c.latitude, longitude: c.longitude, address: c.address } : null;
};
const byId = (id) => state.products.find((p) => p.id === Number(id));
const mapUrl = ({ latitude, longitude }) => `https://www.google.com/maps?q=${latitude},${longitude}`;

async function api(path, options = {}) {
    const response = await fetch(`${API}/${path}`, {
        ...options,
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Telegram-Init-Data': tg?.initData || '' },
    });
    const body = await response.json().catch(() => ({}));
    if (!response.ok) throw Object.assign(new Error(body.message || response.statusText), { status: response.status, body });
    return body;
}

/* ---------- location ---------- */
function currentPosition() {
    return new Promise((resolve, reject) => {
        const manager = tg?.LocationManager;
        if (manager && tg.isVersionAtLeast?.('8.0')) {
            const read = () => manager.getLocation((data) => (data ? resolve({ latitude: data.latitude, longitude: data.longitude }) : reject(new Error('denied'))));
            manager.isInited ? read() : manager.init(read);
            return;
        }
        if (!navigator.geolocation) {
            reject(new Error('unavailable'));
            return;
        }
        navigator.geolocation.getCurrentPosition(
            (position) => resolve({ latitude: position.coords.latitude, longitude: position.coords.longitude }),
            reject,
            { enableHighAccuracy: true, timeout: 15000 },
        );
    });
}

async function pickCurrentLocation() {
    state.locating = true;
    state.locationError = false;
    render();
    try {
        state.pickedLocation = await currentPosition();
        tg?.HapticFeedback?.notificationOccurred('success');
    } catch (e) {
        state.locationError = true;
        tg?.HapticFeedback?.notificationOccurred('error');
    } finally {
        state.locating = false;
        render();
    }
}

function locationPicker({ allowSaved }) {
    const saved = allowSaved ? savedLocation() : null;
    const picked = state.pickedLocation;
    const option = (selected, attrs, body) => `<button type="button" ${attrs} class="flex w-full items-center gap-3 rounded-xl border-2 p-3 text-left ${selected ? 'border-tg-button bg-tg-button/10' : 'border-tg-text/10 bg-tg-surface'}">
        <span class="grid size-5 shrink-0 place-items-center rounded-full border-2 ${selected ? 'border-tg-button bg-tg-button text-tg-button-text' : 'border-tg-text/30'} text-xs">${selected ? '✓' : ''}</span>
        <span class="min-w-0 flex-1">${body}</span></button>`;

    const current = picked && picked !== 'saved'
        ? `<b class="block">${esc(L.locationReceived)}</b><a href="${esc(mapUrl(picked))}" data-link class="text-sm text-tg-link underline">${esc(L.viewOnMap)}</a>`
        : `<b class="block">${esc(state.locating ? L.locating : (saved || savedLocation() ? L.updateLocation : L.shareLocation))}</b>`;

    return `<div class="grid gap-2">
        ${option(picked && picked !== 'saved', 'data-locate', current)}
        ${saved ? option(picked === 'saved', 'data-use-saved', `<b class="block">${esc(L.useSaved)}</b><span class="block truncate text-sm text-tg-hint">${esc(saved.address || `${saved.latitude.toFixed(5)}, ${saved.longitude.toFixed(5)}`)}</span>`) : ''}
        ${state.locationError ? `<div class="rounded-xl bg-tg-danger/10 p-3 text-sm"><p>${esc(L.locationFailed)}</p>${tg?.LocationManager?.isAccessRequested && !tg.LocationManager.isAccessGranted ? `<button type="button" data-location-settings class="mt-2 font-semibold text-tg-link">${esc(L.openSettings)}</button>` : ''}</div>` : ''}
        ${state.errors.latitude ? `<p class="${ui.error}">${esc(state.errors.latitude)}</p>` : ''}
        <label class="${ui.label}"><span>${esc(L.addressDetails)} <span class="${ui.hint}">(${esc(L.optional)})</span></span>
            <input id="address" class="${ui.input}" maxlength="500" value="${esc(state.address)}"></label>
    </div>`;
}

function pickedCoordinates() {
    if (state.pickedLocation === 'saved') return savedLocation();
    return state.pickedLocation;
}

/* ---------- cart ---------- */
const cartKey = `saxiy-webapp-cart-${tgUser.id || 0}`;
try { state.cart = JSON.parse(localStorage.getItem(cartKey) || '{}'); } catch (e) { state.cart = {}; }

function setQty(id, qty) {
    if (qty > 0) state.cart[id] = qty; else delete state.cart[id];
    try { localStorage.setItem(cartKey, JSON.stringify(state.cart)); } catch (e) { /* storage unavailable */ }
    tg?.HapticFeedback?.selectionChanged();
    render();
}

/* ---------- my list (saved on the server, so it follows the customer to any device) ---------- */
const isSaved = (id) => Boolean(state.saved[id]);

function flash(message) {
    state.notice = message;
    tg?.HapticFeedback?.notificationOccurred('success');
    render();
    clearTimeout(flash.timer);
    flash.timer = setTimeout(() => { state.notice = ''; render(); }, 2200);
}

async function saveToList(quantities) {
    const previous = state.saved;
    state.saved = { ...state.saved, ...quantities };
    render();
    try {
        state.saved = (await api('saved', { method: 'POST', body: JSON.stringify({ items: Object.entries(quantities).map(([id, qty]) => ({ product_id: Number(id), quantity: qty })) }) })).saved;
        return true;
    } catch (error) {
        state.saved = previous;
        render();
        return false;
    }
}

async function toggleSaved(id) {
    if (!isSaved(id)) {
        if (await saveToList({ [id]: state.cart[id] || 1 })) flash(L.listSaved);
        return;
    }
    const previous = state.saved;
    const { [id]: removed, ...rest } = state.saved;
    state.saved = rest;
    tg?.HapticFeedback?.selectionChanged();
    render();
    try {
        state.saved = (await api(`saved/${id}`, { method: 'DELETE' })).saved;
    } catch (error) {
        state.saved = previous;
        render();
    }
}

function addToCart(quantities) {
    Object.entries(quantities).forEach(([id, qty]) => {
        if (byId(id)?.orderable) state.cart[id] = (state.cart[id] || 0) + qty;
    });
    try { localStorage.setItem(cartKey, JSON.stringify(state.cart)); } catch (e) { /* storage unavailable */ }
    state.tab = 'cart';
    state.view = null;
    flash(L.addedToCart);
    window.scrollTo(0, 0);
}

const cartLines = () => Object.entries(state.cart).map(([id, qty]) => ({ product: byId(id), qty })).filter((l) => l.product?.orderable);
const cartCount = () => cartLines().reduce((sum, l) => sum + l.qty, 0);
const cartTotal = () => cartLines().reduce((sum, l) => sum + l.qty * l.product.price, 0);

/* ---------- views ---------- */
function picture(p) {
    if (p.image_url) return `<img src="${esc(p.image_url)}" alt="" loading="lazy" class="size-full object-contain">`;
    if (!p.illustration) return '';
    const [art, v] = p.illustration.split(':');
    return `<span class="block aspect-[200/280] w-3/4 [&>svg]:size-full" data-pack="${esc(JSON.stringify({ art, v, band: p.color, w: p.weight, name: name(p.name) }))}"></span>`;
}

function stepper(id, qty, extra = '') {
    return `<div class="flex items-center justify-between rounded-xl bg-tg-button/15 ${extra}">
        <button data-dec="${id}" aria-label="−" class="h-10 w-10 text-xl font-bold text-tg-link">−</button>
        <span class="font-bold">${qty}</span>
        <button data-inc="${id}" aria-label="+" class="h-10 w-10 text-xl font-bold text-tg-link">+</button></div>`;
}

function statusBanner() {
    const status = state.profile?.status;
    if (status === 'active') return isReseller() ? `<span class="mb-3 inline-block rounded-full bg-brand/20 px-3 py-1 text-sm font-bold">${esc(L.resellerBadge)}</span>` : '';
    if (status === 'blocked') return `<div class="mb-3 rounded-2xl bg-tg-danger/10 p-4 text-sm"><b class="block">${esc(L.blockedH)}</b>${esc(L.blockedP)}</div>`;
    return `<div class="mb-3 grid gap-3 rounded-2xl bg-brand/15 p-4 text-sm"><div><b class="block">${esc(L.guestH)}</b>${esc(L.guestP)}</div><button class="${ui.btn}" data-go="register">${esc(L.register)}</button></div>`;
}

function productCard(p) {
    let foot = '';
    if (isActive()) {
        const qty = state.cart[p.id] || 0;
        foot = p.orderable
            ? `<div class="mt-auto pt-1 font-bold">${p.consumer_price && p.consumer_price > p.price ? `<s class="block text-xs font-normal text-tg-hint">${money(p.consumer_price)}</s>` : ''}${money(p.price)} <span class="text-xs font-normal text-tg-hint">/ ${esc(L.perBox)}</span></div>
               ${qty ? stepper(p.id, qty) : `<button class="${ui.btn} py-2.5" data-inc="${p.id}">${esc(L.add)}</button>`}`
            : `<p class="mt-auto text-xs text-tg-hint">${esc(L.unavailable)}</p>`;
    }
    const bookmark = isActive()
        ? `<button data-save="${p.id}" aria-pressed="${isSaved(p.id)}" aria-label="${esc(isSaved(p.id) ? L.removeFromList : L.saveToList)}" class="absolute top-1.5 right-1.5 grid size-9 place-items-center rounded-full bg-tg-bg/85 text-lg shadow-sm active:scale-95 ${isSaved(p.id) ? 'text-brand' : 'text-tg-hint'}">${isSaved(p.id) ? '★' : '☆'}</button>`
        : '';
    return `<article class="${ui.card} flex flex-col overflow-hidden">
        <div class="relative grid aspect-square place-items-center bg-tg-button/5 p-2">${picture(p)}${bookmark}</div>
        <div class="flex flex-1 flex-col gap-1.5 p-3"><h3 class="text-sm leading-snug font-semibold">${esc(name(p.name))}</h3>
        <p class="${ui.hint}">${esc(p.weight)} ${esc(L.g)}${p.box_quantity ? ` · ${p.box_quantity} ${esc(L.pcs)}` : ''}${state.category === 'mine' && state.saved[p.id] ? ` · ${esc(L.usualQty)} ${state.saved[p.id]} ${esc(L.perBox)}` : ''}</p>${foot}</div></article>`;
}

function viewCatalog() {
    const mine = state.category === 'mine';
    const list = state.products.filter((p) => (mine ? isSaved(p.id) : state.category === 'all' || p.category_id === state.category));
    const chip = (key, label) => `<button data-cat="${key}" class="shrink-0 rounded-full px-3.5 py-1.5 text-sm font-semibold ${state.category === key ? 'bg-tg-text text-tg-bg' : 'bg-tg-surface'}">${esc(label)}</button>`;
    const orderableSaved = list.filter((p) => p.orderable);
    const listBar = mine && orderableSaved.length
        ? `<button class="${ui.btn} mb-3" data-add-list>${esc(L.addAllToCart)} · ${money(orderableSaved.reduce((sum, p) => sum + p.price * state.saved[p.id], 0))}</button>`
        : '';
    const favorites = isActive() ? chip('mine', `${L.myList}${Object.keys(state.saved).length ? ` · ${Object.keys(state.saved).length}` : ''}`) : '';
    return `<div class="sticky top-0 z-20 -mx-3 -mt-3 mb-3 flex gap-2 overflow-x-auto bg-tg-bg/95 px-3 py-2.5 shadow-[0_1px_0] shadow-tg-text/10 [scrollbar-width:none] sm:-mx-4 sm:px-4">${favorites}${chip('all', L.all)}${state.categories.map((c) => chip(c.id, name(c.name))).join('')}</div>
        ${statusBanner()}
        ${listBar}
        ${mine && !list.length ? `<p class="py-12 text-center text-sm text-tg-hint">${esc(L.listEmpty)}</p>` : ''}
        <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3">${list.map(productCard).join('')}</div>`;
}

function viewCart() {
    const lines = cartLines();
    if (!lines.length) return `<p class="py-16 text-center text-tg-hint">${esc(L.cartEmpty)}</p>`;
    const saved = savedLocation();
    const delivery = isReseller() && saved
        ? `<div class="${ui.card} p-4 text-sm"><b class="block">📍 ${esc(L.deliverySaved)}</b>
            <span class="block text-tg-hint">${esc(saved.address || `${saved.latitude.toFixed(5)}, ${saved.longitude.toFixed(5)}`)}</span>
            <div class="mt-2 flex gap-4"><a href="${esc(mapUrl(saved))}" data-link class="text-tg-link underline">${esc(L.viewOnMap)}</a><button data-tab="profile" class="text-tg-link underline">${esc(L.changeInProfile)}</button></div></div>`
        : `<h3 class="mb-2 font-bold">${esc(L.deliveryH)}</h3>${locationPicker({ allowSaved: true })}`;

    return `<h2 class="${ui.h2}">${esc(L.cart)}</h2>
        <div class="${ui.card} divide-y divide-tg-text/10">${lines.map(({ product: p, qty }) => `<div class="flex items-center gap-3 p-3">
            <div class="min-w-0 flex-1"><b class="block truncate text-sm">${esc(name(p.name))}</b><span class="${ui.hint}">${esc(p.weight)} ${esc(L.g)} · ${money(p.price)} × ${qty}</span></div>
            ${stepper(p.id, qty, 'w-32 shrink-0')}</div>`).join('')}</div>
        <button class="mt-2 w-full rounded-xl px-4 py-2 text-sm font-semibold text-tg-link active:bg-tg-text/5" data-save-cart>${esc(L.saveCartToList)}</button>
        <div class="flex justify-between px-1 py-4 text-lg font-bold"><span>${esc(L.total)}</span><span>${money(cartTotal())}</span></div>
        <section class="grid gap-3">${delivery}
            <label class="${ui.label}"><span>${esc(L.comment)} <span class="${ui.hint}">(${esc(L.optional)})</span></span><textarea id="comment" rows="2" maxlength="1000" class="${ui.input}">${esc(state.comment)}</textarea></label>
            ${state.errors.order ? `<p class="${ui.error}">${esc(state.errors.order)}</p>` : ''}
            ${tg?.MainButton ? '' : `<button class="${ui.btn}" data-place-order>${esc(L.placeOrder)} · ${money(cartTotal())}</button>`}
        </section>`;
}

function viewOrders() {
    if (!state.orders.length) return `<p class="py-16 text-center text-tg-hint">${esc(L.ordersEmpty)}</p>`;
    const badge = (status) => `<span class="rounded-full px-2 py-0.5 text-xs font-bold ${status === 'cancelled' ? 'bg-tg-danger/15' : 'bg-tg-button/15'}">${esc(L.status[status] || status)}</span>`;
    return `<h2 class="${ui.h2}">${esc(L.orders)}</h2><div class="grid gap-2.5">${state.orders.map((o) => `<article class="${ui.card} p-4 text-sm">
        <div class="flex items-center justify-between gap-2"><b>${esc(o.number)}</b>${badge(o.status)}</div>
        <p class="${ui.hint} mt-1">${new Date(o.created_at).toLocaleString(lang)} · <b class="text-tg-text">${money(o.total)}</b></p>
        <p class="mt-2">${o.items.map((i) => `${esc(i.name)} ${i.weight_grams} ${esc(L.g)} × ${i.quantity}`).join(', ')}</p>
        <a href="${esc(mapUrl(o.delivery))}" data-link class="mt-2 inline-block text-tg-link underline">📍 ${esc(o.delivery.address || L.viewOnMap)}</a>
        ${o.items.some((i) => byId(i.product_id)?.orderable) ? `<button class="${ui.btnGhost} mt-3 py-2.5" data-repeat="${esc(o.number)}">${esc(L.repeatOrder)}</button>` : ''}</article>`).join('')}</div>`;
}

function field(key, label, attrs = '', required = true) {
    const value = state.profile?.client?.[key] ?? (key === 'first_name' ? tgUser.first_name : key === 'last_name' ? tgUser.last_name : '') ?? '';
    return `<label class="${ui.label}"><span>${esc(label)}${required ? '' : ` <span class="${ui.hint}">(${esc(L.optional)})</span>`}</span>
        <input name="${key}" value="${esc(value || '')}" ${required ? 'required' : ''} ${attrs} class="${ui.input}">
        ${state.errors[key] ? `<span class="${ui.error}">${esc(state.errors[key])}</span>` : ''}</label>`;
}

function viewRegister() {
    return `<h2 class="${ui.h2}">${esc(state.profile?.client ? L.editProfile : L.register)}</h2><form id="regForm" novalidate class="grid gap-3">
        ${field('first_name', L.firstName, 'autocomplete="given-name" maxlength="100"')}
        ${field('last_name', L.lastName, 'autocomplete="family-name" maxlength="100"')}
        ${field('phone', L.phone, 'type="tel" inputmode="tel" autocomplete="tel" placeholder="+998 90 123 45 67"')}
        ${field('company_name', L.company, 'autocomplete="organization" maxlength="255"', false)}
        ${field('vat_number', L.vat, 'inputmode="numeric" maxlength="32"', false)}
        ${state.profile?.client ? `<fieldset class="grid gap-2"><legend class="mb-1 text-sm font-semibold">${esc(L.location)} <span class="${ui.hint}">(${esc(L.optional)})</span></legend>${locationPicker({ allowSaved: true })}</fieldset>` : ''}
        ${state.errors.form ? `<p class="${ui.error}">${esc(state.errors.form)}</p>` : ''}
        <button class="${ui.btn}" type="submit">${esc(L.submit)}</button>
        <button class="${ui.btnGhost}" type="button" data-go="back">${esc(L.back)}</button></form>`;
}

function viewProfile() {
    const c = state.profile?.client;
    if (!c) return viewRegister();
    const saved = savedLocation();
    const rows = [[`${L.firstName} / ${L.lastName}`, `${c.first_name} ${c.last_name}`], [L.phone, c.phone], [L.company, c.company_name], [L.vat, c.vat_number],
        [L.prices, L.profileType[state.profile.type]], ['', L.profileStatus[state.profile.status]]].filter(([, v]) => v);
    return `<h2 class="${ui.h2}">${esc(c.company_name || `${c.first_name} ${c.last_name}`)}</h2>
        <div class="${ui.card} divide-y divide-tg-text/10 text-sm">${rows.map(([k, v]) => `<div class="flex justify-between gap-3 p-3"><span class="text-tg-hint">${esc(k)}</span><b class="text-right">${esc(v)}</b></div>`).join('')}
        ${saved ? `<div class="p-3"><span class="block text-tg-hint">${esc(L.location)}</span><a href="${esc(mapUrl(saved))}" data-link class="font-semibold text-tg-link underline">📍 ${esc(saved.address || L.viewOnMap)}</a></div>` : ''}</div>
        ${isActive() ? `<button class="${ui.btnGhost} mt-4" data-go="register">${esc(L.editProfile)}</button>` : ''}`;
}

function renderTabs() {
    const tabs = [['catalog', '🛍', esc(L.catalog)]];
    if (isActive()) tabs.push(['cart', '🧺', `${esc(L.cart)}${cartCount() ? ` <span class="rounded-full bg-brand px-1.5 text-[11px] text-black">${cartCount()}</span>` : ''}`], ['orders', '🧾', esc(L.orders)]);
    tabs.push(['profile', '👤', esc(L.profile)]);
    tabsEl.hidden = false;
    tabsEl.innerHTML = `<div class="mx-auto flex max-w-2xl gap-1 p-1.5">${tabs.map(([key, icon, html]) => {
        const current = state.tab === key && !state.view;
        return `<button data-tab="${key}" ${current ? 'aria-current="page"' : ''} class="flex flex-1 flex-col items-center rounded-xl py-1.5 text-xs font-semibold ${current ? 'bg-tg-text/5 text-tg-text' : 'text-tg-hint'}"><span class="text-lg leading-none">${icon}</span>${html}</button>`;
    }).join('')}</div>`;
}

function renderMainButton() {
    const mb = tg?.MainButton;
    if (!mb) return;
    if (isActive() && state.tab === 'cart' && !state.view && cartLines().length) {
        mb.setText(`${L.placeOrder} · ${money(cartTotal())}`);
        mb.show();
    } else {
        mb.hide();
    }
}

function render() {
    const views = { catalog: viewCatalog, cart: viewCart, orders: viewOrders, profile: viewProfile };
    app.innerHTML = (state.view === 'register' ? viewRegister() : views[state.tab]())
        + (state.notice ? `<div role="status" class="fixed inset-x-3 bottom-24 z-30 mx-auto max-w-sm rounded-xl bg-tg-text px-4 py-3 text-center text-sm font-semibold text-tg-bg shadow-lg">✓ ${esc(state.notice)}</div>` : '');
    renderPacks(app);
    renderTabs();
    renderMainButton();
}

function resetLocationForm() {
    state.pickedLocation = null;
    state.locationError = false;
    state.address = state.profile?.client?.address || '';
}

/* ---------- actions ---------- */
async function submitRegistration(form) {
    state.errors = {};
    const location = pickedCoordinates();
    const data = { ...Object.fromEntries(new FormData(form).entries()), ...(location ? { latitude: location.latitude, longitude: location.longitude, address: state.address || null } : {}) };
    try {
        const wasRegistered = Boolean(state.profile?.client);
        state.profile = await api('profile', { method: 'POST', body: JSON.stringify(data) });
        state.view = null;
        state.tab = wasRegistered ? 'profile' : 'catalog';
        resetLocationForm();
        if (!wasRegistered) await loadCatalog();
        if (isActive()) state.orders = (await api('orders')).data;
        tg?.HapticFeedback?.notificationOccurred('success');
    } catch (error) {
        const errors = error.body?.errors || {};
        state.errors = Object.fromEntries(Object.entries(errors).map(([k, v]) => [k, v[0]]));
        if (errors.longitude && !errors.latitude) state.errors.latitude = errors.longitude[0];
        if (!Object.keys(errors).length) state.errors.form = L.error;
        tg?.HapticFeedback?.notificationOccurred('error');
    }
    render();
}

async function placeOrder() {
    const items = cartLines().map(({ product, qty }) => ({ product_id: product.id, quantity: qty }));
    if (!items.length) return;
    state.errors = {};
    const usesSaved = isReseller() && savedLocation();
    const location = usesSaved ? null : pickedCoordinates();
    if (!usesSaved && !location) {
        state.errors.latitude = L.chooseLocation;
        tg?.HapticFeedback?.notificationOccurred('error');
        render();
        return;
    }
    tg?.MainButton?.showProgress();
    try {
        await api('orders', {
            method: 'POST',
            body: JSON.stringify({ items, comment: state.comment || null, ...(location ? { latitude: location.latitude, longitude: location.longitude, address: state.address || null } : {}) }),
        });
        state.cart = {};
        state.comment = '';
        try { localStorage.removeItem(cartKey); } catch (e) { /* storage unavailable */ }
        [state.profile, { data: state.orders }] = await Promise.all([api('profile'), api('orders')]);
        resetLocationForm();
        state.tab = 'orders';
        tg?.HapticFeedback?.notificationOccurred('success');
        tg?.showAlert?.(L.orderSent);
    } catch (error) {
        const errors = error.body?.errors || {};
        if (errors.latitude) state.errors.latitude = errors.latitude[0];
        else state.errors.order = errors.items?.[0] || error.body?.message || L.error;
        if (error.status === 422 && errors.items) await loadCatalog();
        tg?.HapticFeedback?.notificationOccurred('error');
    } finally {
        tg?.MainButton?.hideProgress();
    }
    render();
}

async function loadCatalog() {
    const catalog = await api('catalog');
    state.categories = catalog.categories;
    state.products = catalog.products;
    state.saved = catalog.saved || {};
}

document.addEventListener('click', (e) => {
    const link = e.target.closest('a[data-link]');
    if (link && tg?.openLink) { e.preventDefault(); tg.openLink(link.href); return; }
    const el = e.target.closest('button');
    if (!el) return;
    if (el.dataset.inc) setQty(el.dataset.inc, (state.cart[el.dataset.inc] || 0) + 1);
    else if (el.dataset.dec) setQty(el.dataset.dec, (state.cart[el.dataset.dec] || 0) - 1);
    else if (el.dataset.cat) { state.category = ['all', 'mine'].includes(el.dataset.cat) ? el.dataset.cat : Number(el.dataset.cat); render(); }
    else if (el.dataset.save) toggleSaved(el.dataset.save);
    else if (el.hasAttribute('data-add-list')) addToCart(Object.fromEntries(Object.entries(state.saved).filter(([id]) => byId(id)?.orderable)));
    else if (el.hasAttribute('data-save-cart')) saveToList(Object.fromEntries(cartLines().map((l) => [l.product.id, l.qty]))).then((ok) => ok && flash(L.listSaved));
    else if (el.dataset.repeat) {
        const order = state.orders.find((o) => o.number === el.dataset.repeat);
        addToCart(Object.fromEntries(order.items.filter((i) => byId(i.product_id)?.orderable).map((i) => [i.product_id, i.quantity])));
    }
    else if (el.dataset.tab) { state.tab = el.dataset.tab; state.view = null; state.errors = {}; render(); window.scrollTo(0, 0); }
    else if (el.dataset.go === 'register') { state.view = 'register'; state.errors = {}; resetLocationForm(); render(); window.scrollTo(0, 0); }
    else if (el.dataset.go === 'back') { state.view = null; state.errors = {}; render(); }
    else if (el.hasAttribute('data-locate')) { if (!state.locating) pickCurrentLocation(); }
    else if (el.hasAttribute('data-use-saved')) { state.pickedLocation = 'saved'; state.locationError = false; delete state.errors.latitude; render(); }
    else if (el.hasAttribute('data-location-settings')) tg?.LocationManager?.openSettings();
    else if (el.hasAttribute('data-place-order')) placeOrder();
});
document.addEventListener('input', (e) => {
    if (e.target.id === 'comment') state.comment = e.target.value;
    if (e.target.id === 'address') state.address = e.target.value;
});
document.addEventListener('submit', (e) => { if (e.target.id === 'regForm') { e.preventDefault(); submitRegistration(e.target); } });
tg?.MainButton?.onClick(placeOrder);

async function boot() {
    if (!tg?.initData) {
        app.innerHTML = `<p class="py-16 text-center text-tg-hint">${esc(T.ru.openInTelegram)}<br><br>${esc(T.uz.openInTelegram)}</p>`;
        return;
    }
    tg.ready();
    tg.expand();
    try {
        const [profile] = await Promise.all([api('profile'), loadCatalog()]);
        state.profile = profile;
        resetLocationForm();
        if (isActive()) state.orders = (await api('orders')).data;
        render();
    } catch (error) {
        app.innerHTML = `<p class="py-16 text-center text-tg-hint">${esc(L.error)}</p>`;
    }
}

boot();
