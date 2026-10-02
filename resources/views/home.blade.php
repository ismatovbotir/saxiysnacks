<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>SAXIY Premium Snacks — kurt, nuts, suluguni</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Onest:wght@400;500;600;700&family=Unbounded:wght@500;600;700&display=swap" rel="stylesheet">
@verbatim
<style>
:root{
  --orange:#F59B00; --orange-d:#B86F00; --green:#2FA354; --green-d:#1E7A3C;
  --bg:#F7FAF3; --surface:#FFFFFF; --ink:#1B2616; --muted:#5B6754; --line:#DDE5D3; --tint:#EAF3E1; --sun:#FFF1D2;
  --blue:#1660C9;
  --display:'Unbounded','Arial Black',system-ui,sans-serif; --body:'Onest',system-ui,-apple-system,'Segoe UI',Arial,sans-serif;
  box-sizing:border-box; padding-top:env(safe-area-inset-top,0px); padding-bottom:env(safe-area-inset-bottom,0px);
}
@media (prefers-color-scheme:dark){:root:not([data-theme="light"]){
  --bg:#11170E; --surface:#182212; --ink:#EEF4E6; --muted:#A3B098; --line:#2B3823; --tint:#1D2A16; --sun:#33280C; --orange-d:#F5B23F; --green-d:#5CCB80;
}}
:root[data-theme="dark"]{
  --bg:#11170E; --surface:#182212; --ink:#EEF4E6; --muted:#A3B098; --line:#2B3823; --tint:#1D2A16; --sun:#33280C; --orange-d:#F5B23F; --green-d:#5CCB80;
}
*,*::before,*::after{box-sizing:inherit}
html{height:100%;scroll-padding-top:env(safe-area-inset-top,0px);scroll-behavior:smooth}
body{margin:0;min-height:100%;background:var(--bg);color:var(--ink);font:400 16px/1.6 var(--body);-webkit-text-size-adjust:100%}
a{color:inherit}
button{font:inherit;color:inherit;cursor:pointer}
:focus-visible{outline:3px solid var(--orange);outline-offset:3px;border-radius:6px}
.wrap{max-width:1180px;margin:0 auto;padding:0 20px}
h1,h2,h3{font-family:var(--display);font-weight:600;line-height:1.12;margin:0;letter-spacing:-.01em}
h1{font-size:clamp(30px,5.2vw,58px)} h2{font-size:clamp(23px,3.2vw,36px)} h3{font-size:17px;line-height:1.3}
p{margin:0}
.muted{color:var(--muted)}
.sr{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)}

/* header */
header.top{position:sticky;top:0;z-index:30;background:color-mix(in srgb,var(--bg) 90%,transparent);backdrop-filter:blur(10px);border-bottom:1px solid var(--line);padding-top:env(safe-area-inset-top,0px)}
.bar{display:flex;align-items:center;gap:18px;height:64px}
.brand{display:flex;align-items:center;gap:10px;text-decoration:none}
.brand svg{width:38px;height:38px}
.brand b{font-family:'Times New Roman',Georgia,serif;font-size:24px;letter-spacing:.04em;font-weight:700}
nav.main{display:flex;gap:4px;margin-left:auto}
nav.main a{padding:8px 12px;border-radius:999px;text-decoration:none;font-weight:500;font-size:15px}
nav.main a:hover,nav.main a.on{background:var(--tint)}
.tools{display:flex;gap:8px;align-items:center}
.seg{display:flex;border:1px solid var(--line);border-radius:999px;overflow:hidden}
.seg button{border:0;background:none;padding:6px 10px;font-size:13px;font-weight:600}
.seg button[aria-pressed="true"]{background:var(--ink);color:var(--bg)}
.iconbtn{position:relative;border:1px solid var(--line);background:var(--surface);border-radius:999px;height:40px;padding:0 14px;display:flex;align-items:center;gap:8px;font-weight:600;font-size:14px}
.iconbtn .n{background:var(--orange);color:#1B2616;border-radius:999px;min-width:22px;height:22px;display:grid;place-items:center;font-size:12px;padding:0 6px}
@media(max-width:820px){nav.main{position:fixed;left:0;right:0;bottom:0;margin:0;justify-content:space-around;background:var(--surface);border-top:1px solid var(--line);padding:6px 6px calc(6px + env(safe-area-inset-bottom,0px));z-index:40}
 nav.main a{flex:1;text-align:center;font-size:13px}
 body{padding-bottom:64px}.tools .lbl{display:none}}

/* buttons */
.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border-radius:999px;padding:13px 22px;font-weight:700;text-decoration:none;border:2px solid transparent;font-size:15px;transition:transform .15s}
.btn:active{transform:scale(.97)}
.btn.pri{background:var(--orange);color:#1B2616}.btn.pri:hover{background:#FFAE1F}
.btn.grn{background:var(--green);color:#fff}.btn.grn:hover{background:var(--green-d)}
.btn.out{border-color:var(--ink);background:none}.btn.out:hover{background:var(--ink);color:var(--bg)}
.btn.sm{padding:9px 16px;font-size:14px}

/* hero */
.hero{position:relative;overflow:hidden;padding:56px 0 72px}
.hero .grid{display:grid;grid-template-columns:1.05fr .95fr;gap:32px;align-items:center}
.hero h1 em{font-style:normal;color:var(--green-d)}
.hero p.lead{font-size:18px;max-width:46ch;margin:20px 0 28px;color:var(--muted)}
.cta-row{display:flex;flex-wrap:wrap;gap:12px}
.facts{display:flex;flex-wrap:wrap;gap:8px 22px;margin-top:30px;font-size:14px;font-weight:600}
.facts span{display:flex;align-items:center;gap:8px}
.facts i{width:10px;height:10px;border-radius:50%;background:var(--green)}
.crescent{position:absolute;right:-140px;top:-120px;width:560px;height:560px;z-index:0;opacity:.95;pointer-events:none}
.hexbox{position:relative;z-index:1;aspect-ratio:1/1;max-width:520px;margin-left:auto;width:100%}
.hex{position:absolute;width:38%;aspect-ratio:1/1.1547;clip-path:polygon(25% 0,75% 0,100% 50%,75% 100%,25% 100%,0 50%);background:var(--surface);display:block;text-decoration:none;transition:transform .25s}
.hex:hover{transform:translateY(-6px)}
.hex svg{position:absolute;inset:4%;width:92%;height:92%}
.hex .cap{position:absolute;left:0;right:0;bottom:12%;text-align:center;font-size:12px;font-weight:700;color:#fff;text-shadow:0 1px 3px rgba(0,0,0,.6)}
.hexshadow{filter:drop-shadow(0 10px 14px rgba(27,38,22,.22))}
@media(max-width:820px){.hero{padding:28px 0 44px}.hero .grid{grid-template-columns:1fr}.hexbox{margin:8px auto 0;max-width:400px}.crescent{width:340px;height:340px;right:-120px;top:-100px}}

/* sections */
section.blk{padding:64px 0}
.head{display:flex;align-items:end;justify-content:space-between;gap:16px;margin-bottom:28px;flex-wrap:wrap}
.head p{max-width:52ch;color:var(--muted);margin-top:10px}
.trust{background:var(--ink);color:#F1F6EA;padding:22px 0}
.trust .row{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}
.trust b{font-family:var(--display);font-size:22px;display:block;color:var(--orange)}
.trust span{font-size:14px;opacity:.85}
@media(max-width:700px){.trust .row{grid-template-columns:1fr 1fr}}

.cats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.cat{position:relative;display:flex;flex-direction:column;border-radius:26px 26px 26px 6px;padding:20px;text-decoration:none;overflow:hidden;min-height:300px;background:var(--tint);transition:transform .2s}
.cat:hover{transform:translateY(-4px)}
.cat:nth-child(2){background:var(--sun)}.cat:nth-child(3){background:#E5EEFA}.cat:nth-child(4){background:#FBEBD3}
:root[data-theme="dark"] .cat:nth-child(3){background:#15243A}
.cat .art{flex:1;display:grid;place-items:center}
.cat .art svg{width:150px;height:auto}
.cat small{color:var(--muted);font-weight:500}
@media(max-width:900px){.cats{grid-template-columns:1fr 1fr}}
@media(max-width:480px){.cat{min-height:230px}.cat .art svg{width:110px}}

.products{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}
@media(max-width:1000px){.products{grid-template-columns:repeat(3,1fr)}}
@media(max-width:720px){.products{grid-template-columns:1fr 1fr;gap:12px}}
.card{background:var(--surface);border:1px solid var(--line);border-radius:20px;overflow:hidden;display:flex;flex-direction:column}
.card a.pic{display:block;background:var(--tint);padding:16px 10px 8px;text-decoration:none;position:relative}
.card a.pic svg{width:100%;max-width:150px;height:auto;display:block;margin:0 auto}
.card .body{padding:14px 16px 16px;display:flex;flex-direction:column;gap:6px;flex:1}
.card .meta{font-size:13px;color:var(--muted)}
.card .foot{margin-top:auto;padding-top:10px;display:flex;gap:8px;align-items:center;justify-content:space-between}
.tag{position:absolute;left:10px;top:10px;background:var(--orange);color:#1B2616;font-size:12px;font-weight:700;border-radius:999px;padding:3px 10px}
.tag.g{background:var(--green);color:#fff}
.price{font-weight:700;font-size:14px}

.why{display:grid;grid-template-columns:repeat(3,1fr);gap:0;border-top:2px solid var(--ink)}
.why > div{padding:24px 24px 24px 0;border-bottom:1px solid var(--line)}
.why > div+div{padding-left:24px;border-left:1px solid var(--line)}
.why h3{margin-bottom:8px}
@media(max-width:820px){.why{grid-template-columns:1fr}.why>div+div{padding-left:0;border-left:0}}

.bundle{background:var(--green);color:#fff;border-radius:34px 34px 34px 8px;padding:40px;display:grid;grid-template-columns:1.2fr .8fr;gap:24px;align-items:center;position:relative;overflow:hidden}
.bundle h2{color:#fff}.bundle p{opacity:.92;margin:12px 0 22px;max-width:48ch}
.bundle ul{list-style:none;margin:0;padding:0;display:grid;gap:10px}
.bundle li{background:rgba(255,255,255,.14);border-radius:14px;padding:12px 16px;display:flex;justify-content:space-between;gap:12px;font-weight:600}
@media(max-width:820px){.bundle{grid-template-columns:1fr;padding:26px}}

.story{display:grid;grid-template-columns:1fr 1fr;gap:36px;align-items:center}
.story .quote{background:var(--sun);border-radius:26px;padding:28px}
@media(max-width:820px){.story{grid-template-columns:1fr}}

/* category page */
.crumbs{font-size:14px;color:var(--muted);padding:20px 0 6px}.crumbs a{text-decoration:none}.crumbs a:hover{text-decoration:underline}
.cathead{padding:8px 0 24px;display:grid;grid-template-columns:1fr auto;gap:18px;align-items:end}
.chips{display:flex;flex-wrap:wrap;gap:8px;margin:0 0 24px}
.chip{border:1.5px solid var(--line);background:var(--surface);border-radius:999px;padding:7px 14px;font-weight:600;font-size:14px}
.chip[aria-pressed="true"]{background:var(--ink);color:var(--bg);border-color:var(--ink)}
select{font:inherit;border:1.5px solid var(--line);background:var(--surface);color:var(--ink);border-radius:12px;padding:9px 12px}
.catnav{display:flex;gap:8px;overflow-x:auto;padding-bottom:10px;margin-bottom:10px}

/* item page */
.item{display:grid;grid-template-columns:1fr 1fr;gap:40px;padding:12px 0 40px}
.stage{background:var(--tint);border-radius:34px 34px 34px 8px;display:grid;place-items:center;padding:36px;position:sticky;top:84px;align-self:start}
.stage svg{width:100%;max-width:260px;height:auto}
.spec{width:100%;border-collapse:collapse;margin:18px 0}
.spec th,.spec td{text-align:left;padding:11px 0;border-bottom:1px solid var(--line);font-size:15px;vertical-align:top}
.spec th{width:42%;color:var(--muted);font-weight:500}
.sizes{display:flex;flex-wrap:wrap;gap:8px;margin:10px 0 20px}
.qty{display:inline-flex;align-items:center;border:1.5px solid var(--line);border-radius:999px;background:var(--surface)}
.qty button{border:0;background:none;width:38px;height:42px;font-size:20px;font-weight:700}
.qty span{min-width:32px;text-align:center;font-weight:700}
.vit{display:flex;gap:8px;flex-wrap:wrap;margin:14px 0}
.vit b{width:38px;height:38px;border-radius:50%;border:2px solid var(--green);display:grid;place-items:center;font-size:14px;color:var(--green-d)}
.note{background:var(--sun);border-radius:14px;padding:12px 16px;font-size:14px}
@media(max-width:820px){.item{grid-template-columns:1fr;gap:20px}.stage{position:static;padding:22px}.stage svg{max-width:200px}}

/* contact */
.contact{display:grid;grid-template-columns:.9fr 1.1fr;gap:32px;padding:16px 0 56px}
.cbox{display:grid;gap:12px;align-content:start}
.cbox a.row,.cbox .row{display:flex;gap:14px;align-items:center;background:var(--surface);border:1px solid var(--line);border-radius:18px;padding:16px 18px;text-decoration:none}
.cbox .ic{width:42px;height:42px;border-radius:50%;background:var(--tint);display:grid;place-items:center;flex:none;font-size:18px}
.cbox b{display:block}
form.f{background:var(--surface);border:1px solid var(--line);border-radius:26px;padding:26px;display:grid;gap:14px}
label{font-weight:600;font-size:14px;display:grid;gap:6px}
input,textarea,select.in{font:inherit;color:var(--ink);background:var(--bg);border:1.5px solid var(--line);border-radius:12px;padding:11px 13px;width:100%}
input:focus,textarea:focus{border-color:var(--green);outline:0}
.two{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.seltype{display:flex;gap:8px;flex-wrap:wrap}
.err{color:#C0392B;font-size:13px;min-height:1em}
@media(max-width:820px){.contact{grid-template-columns:1fr}.two{grid-template-columns:1fr}}

/* inquiry drawer */
.scrim{position:fixed;inset:0;background:rgba(10,16,8,.5);z-index:60;display:none}
.scrim.on{display:block}
.drawer{position:fixed;top:0;right:0;bottom:0;width:min(420px,100%);background:var(--surface);z-index:61;transform:translateX(102%);transition:transform .25s;display:flex;flex-direction:column;padding-top:env(safe-area-inset-top,0px);padding-bottom:env(safe-area-inset-bottom,0px)}
.drawer.on{transform:none}
.drawer header{display:flex;justify-content:space-between;align-items:center;padding:18px 20px;border-bottom:1px solid var(--line)}
.drawer .list{flex:1;overflow:auto;padding:8px 20px}
.li{display:grid;grid-template-columns:1fr auto;gap:8px;padding:14px 0;border-bottom:1px solid var(--line)}
.li .qty button{width:30px;height:34px}
.drawer footer{padding:18px 20px;border-top:1px solid var(--line);display:grid;gap:10px}
.toast{position:fixed;left:50%;bottom:90px;transform:translateX(-50%) translateY(20px);background:var(--ink);color:var(--bg);padding:11px 18px;border-radius:999px;font-weight:600;font-size:14px;opacity:0;pointer-events:none;transition:.25s;z-index:80}
.toast.on{opacity:1;transform:translateX(-50%)}

footer.site{background:var(--ink);color:#DCE6D2;padding:44px 0 30px;margin-top:20px}
footer.site .cols{display:grid;grid-template-columns:1.3fr 1fr 1fr;gap:28px}
footer.site a{text-decoration:none;display:block;padding:3px 0}footer.site a:hover{color:var(--orange)}
footer.site h3{color:#fff;margin-bottom:10px;font-size:15px}
footer .fine{border-top:1px solid #34432b;margin-top:26px;padding-top:16px;font-size:13px;opacity:.75}
@media(max-width:700px){footer.site .cols{grid-template-columns:1fr}}
@media(prefers-reduced-motion:reduce){*{transition:none!important;scroll-behavior:auto!important}}
</style>
@endverbatim
</head>
<body>
<a class="sr" href="#app">Skip to content</a>
<header class="top"><div class="wrap bar">
  <a class="brand" href="#/" aria-label="SAXIY home">
    <svg viewBox="0 0 60 60" aria-hidden="true"><path d="M30 3a27 27 0 1 0 22 42 23 23 0 0 1-18 8A24 24 0 0 1 30 3Z" fill="#F59B00"/><path d="M14 29C18 17 32 9 46 20c-14-3-26 0-32 9Z" fill="#2FA354"/></svg>
    <b>SAXIY</b>
  </a>
  <nav class="main" id="nav" aria-label="Main"></nav>
  <div class="tools">
    <div class="seg" role="group" aria-label="Language">
      <button data-lang="uz">UZ</button><button data-lang="ru">RU</button><button data-lang="en">EN</button>
    </div>
    <button class="iconbtn" id="cartBtn" aria-label="Open inquiry list"><span class="lbl" id="cartLbl"></span><span class="n" id="cartN">0</span></button>
  </div>
</div></header>

<main id="app"></main>

<footer class="site"><div class="wrap">
  <div class="cols">
    <div><h3>SAXIY Premium Snacks</h3><p id="fAbout" style="max-width:38ch;opacity:.85"></p></div>
    <div><h3 id="fShop"></h3><div id="fLinks"></div></div>
    <div><h3 id="fContact"></h3>
      <a href="tel:+998946944499">+998 94 694-44-99</a><a href="tel:+998991774499">+998 99 177-44-99</a>
      <span id="fAddr" style="opacity:.85;display:block;padding-top:6px;font-size:14px"></span></div>
  </div>
  <div class="fine">© 2026 SAXIY · Est. 2017</div>
</div></footer>

<div class="scrim" id="scrim"></div>
<aside class="drawer" id="drawer" aria-label="Inquiry list" aria-hidden="true">
  <header><h3 id="dTitle"></h3><button class="btn out sm" id="dClose"></button></header>
  <div class="list" id="dList"></div>
  <footer id="dFoot"></footer>
</aside>
<div class="toast" id="toast" role="status"></div>

@verbatim
<script>
/* ---------- data (from the 2026 catalog) ---------- */
const PHONE_WA='998946944499';
const T={
 ru:{home:'Главная',catalog:'Каталог',contact:'Контакты',inq:'Заявка',inqOpen:'Заявка',
  heroH:'Натуральные снеки из молока и ядер — по-настоящему',heroEm:'без лишнего',
  heroP:'Био курт, орехи и сулугуни SAXIY: короткий состав, удобные порции 4–160 г, срок годности 6 месяцев. Оптом для магазинов, кафе и АЗС.',
  seeCat:'Смотреть каталог',getPrice:'Запросить прайс',
  f1:'С 2017 года',f2:'Состав: молоко и соль',f3:'Коробки от 30 шт',
  t1:'2017',t1s:'производим в Узбекистане',t2:'6 мес.',t2s:'срок годности курта',t3:'4–160 г',t3s:'порции для любой полки',t4:'100%',t4s:'natural на упаковке био курта',
  catsH:'Четыре линейки — одна полка',catsP:'Выберите категорию и откройте размеры, фасовку в коробке и состав.',
  bestH:'Что берут чаще всего',bestP:'Хиты для импульсной полки — быстрый оборот и понятная цена за штуку.',
  whyH:'Почему SAXIY',
  w1:'Короткий состав',w1p:'Курт — это молоко и йодированная соль. Ничего, что нужно расшифровывать на этикетке.',
  w2:'Порции под задачу',w2p:'От 4 г на кассу до 160 г на семейную полку. Один бренд закрывает все форматы.',
  w3:'Опт без сюрпризов',w3p:'Фиксированное количество в коробке, штрихкоды на упаковке, прямой контакт с производителем.',
  bundleH:'Соберите первую поставку за 5 минут',bundleP:'Добавьте позиции в заявку и отправьте её нам в WhatsApp — менеджер вернётся с ценой и сроками.',
  bundleBtn:'Открыть заявку',
  storyH:'Курт — любимая закуска Центральной Азии',storyP:'Курт (курут) делают из коровьего или козьего молока и сушат. Сушёный молочный продукт содержит кальций, железо, магний и калий. Мы сохранили традиционную технологию и упаковали её в современный формат.',
  quote:'Продукт, называемый курт или курут, является одним из любимых блюд народов Центральной Азии.',
  all:'Все',sort:'Сортировка',sDef:'По умолчанию',sW:'Вес: по возрастанию',sWd:'Вес: по убыванию',
  pieces:'шт. в коробке',weight:'Вес',box:'В коробке',shelf:'Срок годности',comp:'Состав',nutr:'Пищевая ценность',
  months:'6 месяцев',add:'В заявку',added:'Добавлено в заявку',view:'Подробнее',priceOnReq:'Цена по запросу',
  sizes:'Другие размеры',related:'Смотрите также',qty:'Количество, коробок',boxes:'кор.',
  benefits:'Польза по данным каталога',askPrice:'Узнать цену',
  noteItem:'Цены и оптовые условия уточняйте у менеджера — пришлём прайс в день обращения.',
  empty:'Заявка пуста. Добавьте позиции из каталога.',dTitle:'Ваша заявка',close:'Закрыть',
  sendWA:'Отправить в WhatsApp',toCall:'Позвонить',clear:'Очистить',
  cName:'Имя',cPhone:'Телефон',cCompany:'Компания / магазин',cMsg:'Сообщение',cSend:'Отправить в WhatsApp',
  cType:'Кто вы',t_ret:'Магазин',t_hor:'Кафе / HoReCa',t_dist:'Дистрибьютор',t_oth:'Другое',
  cH:'Свяжитесь с нами',cP:'Позвоните или напишите — ответим на запрос прайса в рабочее время.',
  cAddr:'Адрес',cCall:'Телефон',cWA:'WhatsApp',
  errName:'Введите имя',errPhone:'Введите телефон',
  fAbout:'Премиальные снеки из молока, орехов и сыра. Производство в Ташкентской области.',fShop:'Каталог',fContact:'Контакты',
  hi:'Здравствуйте! Хочу запросить прайс SAXIY.',order:'Здравствуйте! Заявка SAXIY:',
  cats:{kurt:'Био курт',classic:'Классический курт',nuts:'Орехи',suluguni:'Сулугуни'},
  catD:{kurt:'Шарики и брикеты в блистере — 30, 47 и 60 г',classic:'Курт 4 г и Tosh Qurt 30 г',nuts:'Арахис, кешью, миндаль, фисташки, косточки',suluguni:'Копчёные палочки 40, 80 и 160 г'},
  catIntro:{kurt:'Молочный, ягодный и ассорти в готовых блистерах. Удобно ставить на кассовую зону и в холодильные витрины.',classic:'Традиционный курт — на развес и в штучной упаковке.',nuts:'Обжаренные ядра в прозрачных пакетах 30 и 60 г. Пять вкусов, один формат.',suluguni:'Тягучий сыр-соломка в трёх размерах — от закуски до семейной пачки.'},
  ben:{kurt:['Кальций — укрепляет кости','B6 — ускоряет обмен жиров','E — противостоит старению','D — снижает риск рака','C — укрепляет иммунитет'],
       classic:['Кальций — укрепляет кости','B6 — ускоряет обмен жиров','E — противостоит старению','D — снижает риск рака','C — укрепляет иммунитет'],
       nuts:['Кальций — укрепляет кости','B6 — ускоряет обмен жиров','E — противостоит старению','D — снижает риск рака','C — укрепляет иммунитет'],
       suluguni:['Кальций — укрепляет кости','A — улучшает иммунитет','E — противостоит старению','D — снижает риск рака','C — укрепляет иммунитет']},
  g:'г',pcs:'шт',compMilk:'молоко, йодированная соль',nutrKurt:'белки 17 г · углеводы 1 г · жиры 5 г',nutrClassic:'белки 2 г · углеводы 1 г · жиры 1 г',
  company:'СП «SAXIY OMAD TRADING»',addr:'Ташкентский район, Ташкентский регион, ул. Пиллакор, д. 55',
  badges:{best:'Хит',new:'Новинка'}},
 en:{home:'Home',catalog:'Catalog',contact:'Contact',inq:'Inquiry',
  heroH:'Natural snacks made from milk and kernels, with',heroEm:'nothing extra',
  heroP:'SAXIY bio kurt, nuts and suluguni: short ingredient lists, portions from 4 to 160 g, 6-month shelf life. Wholesale for shops, cafés and fuel stations.',
  seeCat:'Browse catalog',getPrice:'Request price list',
  f1:'Since 2017',f2:'Just milk and salt',f3:'Boxes from 30 pcs',
  t1:'2017',t1s:'made in Uzbekistan',t2:'6 months',t2s:'kurt shelf life',t3:'4–160 g',t3s:'portions for every shelf',t4:'100%',t4s:'natural label on bio kurt',
  catsH:'Four lines, one shelf',catsP:'Pick a category to see sizes, box quantities and ingredients.',
  bestH:'Most requested',bestP:'Impulse-shelf favourites with fast turnover.',
  whyH:'Why SAXIY',
  w1:'Short ingredient list',w1p:'Kurt is milk and iodized salt. Nothing you need to decode on the label.',
  w2:'Portions for every job',w2p:'From 4 g at the till to 160 g for the family shelf. One brand covers every format.',
  w3:'Wholesale, no surprises',w3p:'Fixed box quantities, barcodes on every pack, direct contact with the producer.',
  bundleH:'Build your first order in 5 minutes',bundleP:'Add items to your inquiry and send it to us on WhatsApp. A manager replies with prices and lead times.',
  bundleBtn:'Open inquiry',
  storyH:'Kurt: a Central Asian favourite',storyP:'Kurt (qurut) is dried milk made from cow or goat milk. It provides calcium, iron, magnesium and potassium. We kept the traditional method and packed it for modern retail.',
  quote:'Kurt, or qurut, is one of the best-loved foods across Central Asia.',
  all:'All',sort:'Sort',sDef:'Default',sW:'Weight: low to high',sWd:'Weight: high to low',
  pieces:'pcs per box',weight:'Weight',box:'Per box',shelf:'Shelf life',comp:'Ingredients',nutr:'Nutrition',
  months:'6 months',add:'Add to inquiry',added:'Added to inquiry',view:'Details',priceOnReq:'Price on request',
  sizes:'Other sizes',related:'You may also like',qty:'Boxes',boxes:'boxes',
  benefits:'Benefits (per catalog)',askPrice:'Ask for price',
  noteItem:'Ask the manager for wholesale terms. We send the price list the same day.',
  empty:'Your inquiry is empty. Add items from the catalog.',dTitle:'Your inquiry',close:'Close',
  sendWA:'Send on WhatsApp',toCall:'Call us',clear:'Clear',
  cName:'Name',cPhone:'Phone',cCompany:'Company / shop',cMsg:'Message',cSend:'Send on WhatsApp',
  cType:'You are a',t_ret:'Retail shop',t_hor:'Café / HoReCa',t_dist:'Distributor',t_oth:'Other',
  cH:'Get in touch',cP:'Call or write. We answer price requests during business hours.',
  cAddr:'Address',cCall:'Phone',cWA:'WhatsApp',
  errName:'Enter your name',errPhone:'Enter your phone number',
  fAbout:'Premium snacks from milk, nuts and cheese. Made in the Tashkent region.',fShop:'Catalog',fContact:'Contact',
  hi:'Hello! I would like to request the SAXIY price list.',order:'Hello! SAXIY inquiry:',
  cats:{kurt:'Bio kurt',classic:'Classic kurt',nuts:'Nuts',suluguni:'Suluguni'},
  catD:{kurt:'Balls and bars in blister packs: 30, 47 and 60 g',classic:'Kurt 4 g and Tosh Qurt 30 g',nuts:'Peanut, cashew, almond, pistachio, kernels',suluguni:'Smoked sticks of 40, 80 and 160 g'},
  catIntro:{kurt:'Milk, berry and assorted in ready-to-shelf blisters. Fits checkout zones and chilled displays.',classic:'Traditional kurt, sold as single pieces or long sticks.',nuts:'Roasted kernels in clear 30 g and 60 g bags. Five flavours, one format.',suluguni:'Stringy cheese sticks in three sizes, from a snack to a family pack.'},
  ben:{kurt:['Calcium: strengthens bones','B6: speeds up fat metabolism','E: fights ageing','D: lowers cancer risk','C: strengthens immunity'],
       classic:['Calcium: strengthens bones','B6: speeds up fat metabolism','E: fights ageing','D: lowers cancer risk','C: strengthens immunity'],
       nuts:['Calcium: strengthens bones','B6: speeds up fat metabolism','E: fights ageing','D: lowers cancer risk','C: strengthens immunity'],
       suluguni:['Calcium: strengthens bones','A: improves immunity','E: fights ageing','D: lowers cancer risk','C: strengthens immunity']},
  g:'g',pcs:'pcs',compMilk:'milk, iodized salt',nutrKurt:'protein 17 g · carbs 1 g · fat 5 g',nutrClassic:'protein 2 g · carbs 1 g · fat 1 g',
  company:'JV "SAXIY OMAD TRADING"',addr:'55 Pillakor St, Tashkent District, Tashkent Region',
  badges:{best:'Bestseller',new:'New'}},
 uz:{home:'Bosh sahifa',catalog:'Katalog',contact:'Aloqa',inq:'Buyurtma',
  heroH:"Sut va yong'oq mag'zidan tabiiy sneklar —",heroEm:'hech qanday ortiqchasiz',
  heroP:"SAXIY bio qurt, yong'oqlar va suluguni: qisqa tarkib, 4–160 g qulay porsiyalar, 6 oylik yaroqlilik muddati. Do'konlar, kafelar va YoQSHlar uchun ulgurji.",
  seeCat:"Katalogni ko'rish",getPrice:"Narxlar ro'yxatini so'rash",
  f1:'2017-yildan beri',f2:'Tarkibi: sut va tuz',f3:'Qutida 30 donadan',
  t1:'2017',t1s:"O'zbekistonda ishlab chiqaramiz",t2:'6 oy',t2s:'qurtning yaroqlilik muddati',t3:'4–160 g',t3s:'har qanday javon uchun porsiyalar',t4:'100%',t4s:"bio qurt qadog'ida natural",
  catsH:"To'rt yo'nalish — bitta javon",catsP:"Toifani tanlang: o'lchamlar, qutidagi soni va tarkibini ko'ring.",
  bestH:"Eng ko'p so'raladiganlar",bestP:'Kassa oldi javoni uchun xitlar — tez aylanma va tushunarli dona narxi.',
  whyH:'Nega SAXIY',
  w1:'Qisqa tarkib',w1p:"Qurt — bu sut va yodlangan tuz. Yorliqda tushunarsiz hech narsa yo'q.",
  w2:'Har ehtiyojga porsiya',w2p:'Kassa uchun 4 g dan oilaviy javon uchun 160 g gacha. Bitta brend barcha formatlarni qamrab oladi.',
  w3:'Kutilmagan holatlarsiz ulgurji',w3p:"Qutidagi aniq miqdor, qadoqda shtrix-kod, ishlab chiqaruvchi bilan to'g'ridan-to'g'ri aloqa.",
  bundleH:"Birinchi buyurtmani 5 daqiqada yig'ing",bundleP:"Mahsulotlarni buyurtmaga qo'shing va WhatsApp orqali yuboring — menejer narx va muddatlar bilan javob beradi.",
  bundleBtn:'Buyurtmani ochish',
  storyH:'Qurt — Markaziy Osiyoning sevimli gazagi',storyP:"Qurt sigir yoki echki sutidan tayyorlanib, quritiladi. Quritilgan sut mahsuloti kalsiy, temir, magniy va kaliyga boy. Biz an'anaviy texnologiyani saqlab, uni zamonaviy qadoqqa joyladik.",
  quote:'Qurt yoki qurut deb ataladigan mahsulot Markaziy Osiyo xalqlarining eng sevimli taomlaridan biridir.',
  all:'Barchasi',sort:'Saralash',sDef:'Standart',sW:"Vazn: o'sish bo'yicha",sWd:"Vazn: kamayish bo'yicha",
  pieces:'dona qutida',weight:'Vazn',box:'Qutida',shelf:'Yaroqlilik muddati',comp:'Tarkibi',nutr:'Ozuqaviy qiymati',
  months:'6 oy',add:'Buyurtmaga',added:"Buyurtmaga qo'shildi",view:'Batafsil',priceOnReq:"Narxi so'rov bo'yicha",
  sizes:"Boshqa o'lchamlar",related:"Shuningdek ko'ring",qty:'Miqdori, quti',boxes:'quti',
  benefits:"Katalog ma'lumotlariga ko'ra foydasi",askPrice:'Narxini bilish',
  noteItem:"Narxlar va ulgurji shartlarni menejerdan aniqlang — narxlar ro'yxatini murojaat kuniyoq yuboramiz.",
  empty:"Buyurtma bo'sh. Katalogdan mahsulot qo'shing.",dTitle:'Sizning buyurtmangiz',close:'Yopish',
  sendWA:'WhatsApp orqali yuborish',toCall:"Qo'ng'iroq qilish",clear:'Tozalash',
  cName:'Ism',cPhone:'Telefon',cCompany:"Kompaniya / do'kon",cMsg:'Xabar',cSend:'WhatsApp orqali yuborish',
  cType:'Siz kimsiz',t_ret:"Do'kon",t_hor:'Kafe / HoReCa',t_dist:'Distribyutor',t_oth:'Boshqa',
  cH:"Biz bilan bog'laning",cP:"Qo'ng'iroq qiling yoki yozing — narx so'rovlariga ish vaqtida javob beramiz.",
  cAddr:'Manzil',cCall:'Telefon',cWA:'WhatsApp',
  errName:'Ismingizni kiriting',errPhone:'Telefon raqamingizni kiriting',
  fAbout:"Sut, yong'oq va pishloqdan premium sneklar. Toshkent viloyatida ishlab chiqariladi.",fShop:'Katalog',fContact:'Aloqa',
  hi:"Assalomu alaykum! SAXIY narxlar ro'yxatini so'ramoqchiman.",order:'Assalomu alaykum! SAXIY buyurtmasi:',
  cats:{kurt:'Bio qurt',classic:'Klassik qurt',nuts:"Yong'oqlar",suluguni:'Suluguni'},
  catD:{kurt:'Blisterdagi sharchalar va briketlar — 30, 47 va 60 g',classic:'Qurt 4 g va Tosh Qurt 30 g',nuts:"Yeryong'oq, keshyu, bodom, pista, o'rik danagi",suluguni:'Dudlangan tayoqchalar — 40, 80 va 160 g'},
  catIntro:{kurt:'Sutli, rezavorli va assorti — tayyor blisterlarda. Kassa zonasi va sovutgichli vitrinalar uchun qulay.',classic:"An'anaviy qurt — donalab va uzun qadoqda.",nuts:"Shaffof 30 va 60 g paketlardagi qovurilgan mag'izlar. Besh ta'm, bitta format.",suluguni:"Cho'ziluvchan pishloq tayoqchalari uch o'lchamda — gazakdan oilaviy qadoqqacha."},
  ben:{kurt:["Kalsiy — suyaklarni mustahkamlaydi","B6 — yog'lar almashinuvini tezlashtiradi",'E — qarishga qarshi kurashadi','D — saraton xavfini kamaytiradi','C — immunitetni mustahkamlaydi'],
       classic:["Kalsiy — suyaklarni mustahkamlaydi","B6 — yog'lar almashinuvini tezlashtiradi",'E — qarishga qarshi kurashadi','D — saraton xavfini kamaytiradi','C — immunitetni mustahkamlaydi'],
       nuts:["Kalsiy — suyaklarni mustahkamlaydi","B6 — yog'lar almashinuvini tezlashtiradi",'E — qarishga qarshi kurashadi','D — saraton xavfini kamaytiradi','C — immunitetni mustahkamlaydi'],
       suluguni:["Kalsiy — suyaklarni mustahkamlaydi",'A — immunitetni yaxshilaydi','E — qarishga qarshi kurashadi','D — saraton xavfini kamaytiradi','C — immunitetni mustahkamlaydi']},
  g:'g',pcs:'dona',compMilk:'sut, yodlangan tuz',nutrKurt:"oqsil 17 g · uglevod 1 g · yog' 5 g",nutrClassic:"oqsil 2 g · uglevod 1 g · yog' 1 g",
  company:'«SAXIY OMAD TRADING» QK',addr:"Toshkent viloyati, Toshkent tumani, Pillakor ko'chasi, 55-uy",
  badges:{best:'Xit',new:'Yangi'}}
};
const CATS=['kurt','classic','nuts','suluguni'];
const NUT={peanut:{ru:'Арахис',en:'Peanut',uz:"Yeryong'oq",c:'#E5C27A'},cashew:{ru:'Кешью',en:'Cashew',uz:'Keshyu',c:'#EBD3A0'},almond:{ru:'Миндаль',en:'Almond',uz:'Bodom',c:'#8B4A24'},pista:{ru:'Фисташки',en:'Pistachio',uz:'Pista',c:'#C9BE85'},pit:{ru:'Косточки',en:'Apricot kernels',uz:"O'rik danagi",c:'#A5623A'}};
const P=[];
const add=(o)=>P.push(o);
// bio kurt
add({id:'bk-30-40',cat:'kurt',w:30,box:40,art:'ball',v:'plain',band:'#1660C9',ru:'Био курт молочный',en:'Bio kurt, milk',uz:'Bio qurt, sutli',best:1});
add({id:'bk-47-30',cat:'kurt',w:47,box:30,art:'ball',v:'plain',band:'#1660C9',ru:'Био курт молочный',en:'Bio kurt, milk',uz:'Bio qurt, sutli'});
add({id:'bk-30-30sq',cat:'kurt',w:30,box:30,art:'square',v:'plain',band:'#1660C9',ru:'Био курт молочный, брикет',en:'Bio kurt, milk bars',uz:'Bio qurt, sutli briket'});
add({id:'bk-30-40mix',cat:'kurt',w:30,box:40,art:'ball',v:'mix',band:'#1660C9',ru:'Био курт ассорти',en:'Bio kurt, assorted',uz:'Bio qurt, assorti',best:1});
add({id:'bk-60-30',cat:'kurt',w:60,box:30,art:'ball',v:'plain',band:'#1660C9',ru:'Био курт молочный',en:'Bio kurt, milk',uz:'Bio qurt, sutli'});
add({id:'bk-60-30berry',cat:'kurt',w:60,box:30,art:'ball',v:'berry',band:'#1660C9',ru:'Био курт ягодный',en:'Bio kurt, berry',uz:'Bio qurt, rezavorli',isnew:1});
// classic
add({id:'ck-4',cat:'classic',w:4,art:'ball1',v:'plain',band:'#159E96',ru:'Курт SAXIY',en:'SAXIY kurt',uz:'SAXIY qurt',best:1});
add({id:'ck-tosh',cat:'classic',w:30,art:'stick1',v:'plain',band:'#E1B22A',ru:'Tosh Qurt',en:'Tosh Qurt',uz:'Tosh Qurt'});
// nuts
[['peanut'],['cashew'],['almond'],['pista'],['pit']].forEach(([k])=>add({id:'nut-'+k+'-30',cat:'nuts',nut:k,w:30,art:'nut',v:k,band:'#F2B705',ru:NUT[k].ru,en:NUT[k].en,uz:NUT[k].uz,best:(k==='cashew'||k==='pista')?1:0}));
['cashew','pit','pista','almond'].forEach(k=>add({id:'nut-'+k+'-60',cat:'nuts',nut:k,w:60,art:'nut',v:k,band:'#F2B705',ru:NUT[k].ru,en:NUT[k].en,uz:NUT[k].uz}));
// suluguni
[40,80,160].forEach(w=>add({id:'sul-'+w,cat:'suluguni',w,art:'stick',v:'sul',band:'#F2A900',ru:'Сулугуни',en:'Suluguni',uz:'Suluguni',best:w===80?1:0}));
const byId=Object.fromEntries(P.map(p=>[p.id,p]));

/* ---------- state ---------- */
const LANGS=['uz','ru','en'];
let lang=(()=>{let saved=null;try{saved=localStorage.getItem('saxiy-lang')}catch(e){}if(LANGS.includes(saved))return saved;const nav=(navigator.language||'').toLowerCase().slice(0,2);return LANGS.includes(nav)?nav:'uz'})();
const cart={}; // id -> boxes
let filterW='all', sortBy='def';
const t=()=>T[lang];
const $=s=>document.querySelector(s);
const nm=p=>p[lang];

/* ---------- illustrations ---------- */
function contents(p){
  let s='';
  if(p.art==='ball'||p.art==='ball1'){
    const pos=p.art==='ball1'?[[100,120]]:[[68,88],[132,88],[68,140],[132,140],[68,192],[132,192]];
    pos.forEach(([x,y],i)=>{
      let f='#F4EEDD',extra='';
      if(p.v==='mix'){f=i<2?'#EFE2E6':i<4?'#F6F1E3':'#EBB99B'}
      const r=p.art==='ball1'?38:26;
      s+=`<circle cx="${x}" cy="${y}" r="${r}" fill="${f}" stroke="#D8CFB8" stroke-width="1.5"/>`;
      if(p.v==='berry'||(p.v==='mix'&&i<2)){for(let k=0;k<7;k++){const a=k*2.3+i,d=(k%3+1)*7;s+=`<circle cx="${x+Math.cos(a)*d}" cy="${y+Math.sin(a)*d}" r="2.6" fill="#5B2A63"/>`}}
      if(p.v==='mix'&&i>=4){for(let k=0;k<6;k++){s+=`<circle cx="${x+Math.cos(k*2)*11}" cy="${y+Math.sin(k*2)*11}" r="2" fill="#D98A66"/>`}}
      s+=`<ellipse cx="${x-r*.3}" cy="${y-r*.4}" rx="${r*.35}" ry="${r*.2}" fill="#fff" opacity=".55"/>`;
    });
  }
  if(p.art==='square'){
    [[56,72],[104,72],[56,124],[104,124],[56,176],[104,176]].forEach(([x,y])=>{s+=`<rect x="${x}" y="${y}" width="44" height="46" rx="4" fill="#F2EBD6" stroke="#D8CFB8"/>`;for(let k=0;k<9;k++)s+=`<circle cx="${x+6+(k*13)%34}" cy="${y+8+(k*7)%32}" r="1.6" fill="#DAD0B4"/>`});
  }
  if(p.art==='nut'){
    const c=NUT[p.v].c;
    for(let i=0;i<11;i++){
      const x=52+(i%3)*48+((Math.floor(i/3))%2)*12, y=80+Math.floor(i/3)*38;
      const rot=(i*37)%80-40;
      if(p.v==='cashew') s+=`<path transform="rotate(${rot} ${x} ${y})" d="M${x-16} ${y+2}c2-14 22-16 30-4 3 5-2 9-6 6-6-6-14-4-15 6-1 5-9 5-9-8Z" fill="${c}" stroke="#B99753" stroke-width="1.2"/>`;
      else if(p.v==='pista') s+=`<g transform="rotate(${rot} ${x} ${y})"><ellipse cx="${x}" cy="${y}" rx="15" ry="10" fill="${c}" stroke="#8E8348" stroke-width="1.2"/><ellipse cx="${x+3}" cy="${y}" rx="8" ry="6" fill="#9DB35B"/></g>`;
      else s+=`<ellipse transform="rotate(${rot} ${x} ${y})" cx="${x}" cy="${y}" rx="${p.v==='peanut'?13:15}" ry="${p.v==='peanut'?9:10}" fill="${c}" stroke="rgba(0,0,0,.25)" stroke-width="1.2"/>`;
    }
  }
  if(p.art==='stick'){
    for(let i=0;i<8;i++){const x=48+i*13;s+=`<rect x="${x}" y="${62+(i%2)*5}" width="10" height="${150-(i%3)*8}" rx="5" fill="${i%3===0?'#E9C777':'#DDAE4A'}" stroke="#B8862C" stroke-width="1"/>`}
  }
  if(p.art==='stick1'){
    s+=`<rect x="78" y="56" width="44" height="170" rx="10" fill="#F6F3E8" stroke="#D9D2BC"/><text x="100" y="140" transform="rotate(-90 100 140)" text-anchor="middle" font-family="Georgia,serif" font-weight="700" font-size="16" fill="#5D5B4E" letter-spacing="2">TOSH QURT</text>`;
  }
  return s;
}
function pack(p,big){
  const label=p.cat==='kurt'?'BIOKURT':p.cat==='nuts'?nm(p).toUpperCase():p.cat==='suluguni'?'СУЛУГУНИ':'KURT';
  const dark=(p.cat==='suluguni');
  return `<svg viewBox="0 0 200 280" role="img" aria-label="${nm(p)} ${p.w} g" xmlns="http://www.w3.org/2000/svg">
  <ellipse cx="100" cy="270" rx="70" ry="6" fill="#000" opacity=".12"/>
  <rect x="20" y="6" width="160" height="258" rx="8" fill="${dark?'#2A2A2A':'#fff'}" stroke="rgba(0,0,0,.12)"/>
  <path d="M20 6h160v50c-30 16-100 16-160 0Z" fill="${p.band}"/>
  <path d="M84 30a16 16 0 1 0 12 26 14 14 0 0 1-12-26Z" fill="#F59B00"/><path d="M78 38c6-6 14-7 22-1-7-1-16 0-22 1Z" fill="#2FA354"/>
  <text x="116" y="42" font-family="Times New Roman,serif" font-weight="700" font-size="17" fill="#fff" text-anchor="middle" style="paint-order:stroke" stroke="rgba(0,0,0,.25)" stroke-width="1">SAXIY</text>
  <text x="100" y="72" text-anchor="middle" font-family="Arial,sans-serif" font-weight="800" font-size="9" letter-spacing="1" fill="${dark?'#F2A900':p.band==='#F2B705'?'#7A5A00':p.band}">${label}</text>
  <rect x="32" y="80" width="136" height="150" rx="6" fill="${dark?'#3B3B3B':'#EEF0EA'}"/>
  ${contents(p)}
  <rect x="20" y="236" width="160" height="28" rx="0" fill="${p.band}"/><path d="M20 264h160" stroke="none"/>
  <text x="100" y="255" text-anchor="middle" font-family="Arial,sans-serif" font-weight="800" font-size="14" fill="#fff">${p.w}±${p.w===4?2:5} g</text>
</svg>`;
}
const catArt=k=>pack(P.find(p=>p.cat===k&&(k!=='kurt'||p.id==='bk-30-40')&&(k!=='nuts'||p.nut==='cashew')&&(k!=='suluguni'||p.w===80)));

/* ---------- helpers ---------- */
const ico=(k)=>t().cats[k];
const wtxt=p=>`${p.w}±${p.w===4?2:5} ${t().g}`;
const boxtxt=p=>p.box?`${p.box} ${t().pieces}`:'';
function toast(msg){const el=$('#toast');el.textContent=msg;el.classList.add('on');clearTimeout(toast.h);toast.h=setTimeout(()=>el.classList.remove('on'),1800)}
function addCart(id,n=1){cart[id]=(cart[id]||0)+n;if(cart[id]<=0)delete cart[id];renderCart();}
function cardHTML(p){
  const b=p.best?`<span class="tag">${t().badges.best}</span>`:p.isnew?`<span class="tag g">${t().badges.new}</span>`:'';
  return `<article class="card"><a class="pic" href="#/item/${p.id}" aria-label="${nm(p)} ${wtxt(p)}">${b}${pack(p)}</a>
   <div class="body"><h3>${nm(p)}</h3><div class="meta">${wtxt(p)}${p.box?' · '+boxtxt(p):''}</div>
   <div class="foot"><span class="price">${t().priceOnReq}</span><button class="btn grn sm" data-add="${p.id}">${t().add}</button></div></div></article>`;
}

/* ---------- views ---------- */
function vHome(){
  const L=t();
  const hexes=[['bk-30-40',5,4],['nut-pista-30',33,0],['nut-cashew-30',61,4],['nut-almond-30',19,29],['bk-60-30berry',47,29],['sul-80',33,56]];
  const hx=hexes.map(([id,l,tp])=>`<a class="hex hexshadow" style="left:${l}%;top:${tp}%;background:${byId[id].cat==='suluguni'?'#F6D77F':byId[id].cat==='kurt'?'#DCE9FA':'#F7E3B5'}" href="#/item/${id}" aria-label="${nm(byId[id])}">${pack(byId[id])}</a>`).join('');
  const best=P.filter(p=>p.best).slice(0,8).map(cardHTML).join('');
  return `
  <section class="hero"><svg class="crescent" viewBox="0 0 560 560" aria-hidden="true"><path d="M300 20C130 10 10 150 30 320c20 170 200 260 340 200-130 20-250-70-240-210C140 190 200 60 300 20Z" fill="#F59B00"/><path d="M420 200c-90-60-190-30-250 60 90-30 190-20 250-60Z" fill="#2FA354" transform="translate(70 30)"/></svg>
   <div class="wrap grid"><div>
     <h1>${L.heroH} <em>${L.heroEm}</em></h1>
     <p class="lead">${L.heroP}</p>
     <div class="cta-row"><a class="btn pri" href="#/catalog">${L.seeCat}</a><a class="btn out" href="#/contact">${L.getPrice}</a></div>
     <div class="facts"><span><i></i>${L.f1}</span><span><i></i>${L.f2}</span><span><i></i>${L.f3}</span></div>
   </div>
   <div class="hexbox">${hx}</div></div></section>
  <div class="trust"><div class="wrap row">${[1,2,3,4].map(i=>`<div><b>${L['t'+i]}</b><span>${L['t'+i+'s']}</span></div>`).join('')}</div></div>
  <section class="blk"><div class="wrap"><div class="head"><div><h2>${L.catsH}</h2><p>${L.catsP}</p></div></div>
   <div class="cats">${CATS.map(k=>`<a class="cat" href="#/catalog/${k}"><div class="art">${catArt(k)}</div><h3>${L.cats[k]}</h3><small>${L.catD[k]}</small></a>`).join('')}</div></div></section>
  <section class="blk" style="padding-top:8px"><div class="wrap"><div class="head"><div><h2>${L.bestH}</h2><p>${L.bestP}</p></div><a class="btn out sm" href="#/catalog">${L.seeCat}</a></div>
   <div class="products">${best}</div></div></section>
  <section class="blk" style="padding-top:8px"><div class="wrap"><h2 style="margin-bottom:26px">${L.whyH}</h2>
   <div class="why"><div><h3>${L.w1}</h3><p class="muted">${L.w1p}</p></div><div><h3>${L.w2}</h3><p class="muted">${L.w2p}</p></div><div><h3>${L.w3}</h3><p class="muted">${L.w3p}</p></div></div></div></section>
  <section class="blk" style="padding-top:8px"><div class="wrap story"><div><h2>${L.storyH}</h2><p class="muted" style="margin-top:14px;max-width:52ch">${L.storyP}</p></div><div class="quote"><p style="font-size:19px;font-weight:600;line-height:1.45">${L.quote}</p></div></div></section>
  <section class="blk" style="padding-top:8px"><div class="wrap"><div class="bundle"><div><h2>${L.bundleH}</h2><p>${L.bundleP}</p><button class="btn pri" data-open="1">${L.bundleBtn}</button></div>
   <ul>${['bk-30-40','nut-cashew-30','sul-80'].map(id=>`<li><span>${nm(byId[id])} ${wtxt(byId[id])}</span><button class="btn pri sm" data-add="${id}">+</button></li>`).join('')}</ul></div></div></section>`;
}
function vCatalog(k){
  const L=t();
  let list=P.filter(p=>!k||p.cat===k);
  const ws=[...new Set(list.map(p=>p.w))].sort((a,b)=>a-b);
  if(filterW!=='all'&&!ws.includes(+filterW))filterW='all';
  if(filterW!=='all')list=list.filter(p=>p.w==+filterW);
  if(sortBy==='w')list=[...list].sort((a,b)=>a.w-b.w);
  if(sortBy==='wd')list=[...list].sort((a,b)=>b.w-a.w);
  const title=k?L.cats[k]:L.catalog;
  return `<div class="wrap"><div class="crumbs"><a href="#/">${L.home}</a> / ${k?`<a href="#/catalog">${L.catalog}</a> / ${title}`:title}</div>
   <div class="cathead"><div><h1 style="font-size:clamp(28px,4vw,44px)">${title}</h1><p class="muted" style="margin-top:10px;max-width:56ch">${k?L.catIntro[k]:L.catsP}</p></div>
   <label>${L.sort}<select id="sortSel"><option value="def">${L.sDef}</option><option value="w">${L.sW}</option><option value="wd">${L.sWd}</option></select></label></div>
   <div class="catnav"><button class="chip" aria-pressed="${!k}" data-go="#/catalog">${L.all}</button>${CATS.map(c=>`<button class="chip" aria-pressed="${k===c}" data-go="#/catalog/${c}">${L.cats[c]}</button>`).join('')}</div>
   <div class="chips" role="group" aria-label="${L.weight}"><button class="chip" aria-pressed="${filterW==='all'}" data-w="all">${L.all}</button>${ws.map(w=>`<button class="chip" aria-pressed="${filterW==w}" data-w="${w}">${w} ${L.g}</button>`).join('')}</div>
   <div class="products" style="margin-bottom:56px">${list.map(cardHTML).join('')}</div></div>`;
}
function vItem(id){
  const p=byId[id]; if(!p)return vCatalog();
  const L=t();
  const sibs=P.filter(q=>q.cat===p.cat&&q.ru===p.ru&&q.id!==p.id);
  const sameFam=P.filter(q=>q.cat===p.cat&&q.nut===p.nut&&q.id!==p.id&&q.ru===p.ru);
  const rel=P.filter(q=>q.cat!==p.cat&&q.best).slice(0,4);
  const nutr=p.cat==='kurt'?L.nutrKurt:p.id==='ck-4'?L.nutrClassic:null;
  const comp=(p.cat==='kurt'||p.cat==='classic')?L.compMilk:null;
  const rows=[[L.weight,wtxt(p)],p.box?[L.box,boxtxt(p)]:null,comp?[L.comp,comp]:null,nutr?[L.nutr,nutr]:null,(p.cat==='kurt'||p.cat==='classic')?[L.shelf,L.months]:null].filter(Boolean);
  const vit=(p.cat==='suluguni'?['Ca','A','E','D','C']:['Ca','B6','E','D','C']);
  return `<div class="wrap"><div class="crumbs"><a href="#/">${L.home}</a> / <a href="#/catalog">${L.catalog}</a> / <a href="#/catalog/${p.cat}">${L.cats[p.cat]}</a> / ${nm(p)}</div>
   <div class="item"><div class="stage">${pack(p,1)}</div>
   <div><h1 style="font-size:clamp(26px,3.6vw,40px)">${nm(p)}</h1><p class="muted" style="margin-top:8px">${wtxt(p)}${p.box?' · '+boxtxt(p):''}</p>
    <table class="spec"><tbody>${rows.map(r=>`<tr><th>${r[0]}</th><td>${r[1]}</td></tr>`).join('')}</tbody></table>
    ${sameFam.length||sibs.length?`<div><b>${L.sizes}</b><div class="sizes">${[...new Set([...sameFam,...sibs])].map(q=>`<a class="chip" style="text-decoration:none" href="#/item/${q.id}">${wtxt(q)}${q.box?' · '+q.box+' '+L.pcs:''}</a>`).join('')}</div></div>`:''}
    <div class="vit" aria-label="${L.benefits}">${vit.map(v=>`<b>${v}</b>`).join('')}</div>
    <ul class="muted" style="margin:0 0 20px;padding-left:18px;font-size:14px">${L.ben[p.cat].map(x=>`<li>${x}</li>`).join('')}</ul>
    <div class="cta-row" style="align-items:center"><div class="qty" id="iq"><button aria-label="−" data-q="-1">−</button><span id="iqv">1</span><button aria-label="+" data-q="1">+</button></div>
     <button class="btn grn" id="iadd" data-id="${p.id}">${L.add}</button><a class="btn out" href="#/contact">${L.askPrice}</a></div>
    <p class="note" style="margin-top:20px">${L.noteItem}</p></div></div>
   <section style="padding:20px 0 56px"><h2 style="margin-bottom:20px;font-size:24px">${L.related}</h2><div class="products">${rel.map(cardHTML).join('')}</div></section></div>`;
}
function vContact(){
  const L=t();
  return `<div class="wrap"><div class="crumbs"><a href="#/">${L.home}</a> / ${L.contact}</div>
  <div style="padding:8px 0 22px"><h1 style="font-size:clamp(28px,4vw,44px)">${L.cH}</h1><p class="muted" style="margin-top:10px;max-width:52ch">${L.cP}</p></div>
  <div class="contact"><div class="cbox">
    <a class="row" href="tel:+998946944499"><span class="ic">☎</span><span><b>${L.cCall}</b>+998 94 694-44-99</span></a>
    <a class="row" href="tel:+998991774499"><span class="ic">☎</span><span><b>${L.cCall}</b>+998 99 177-44-99</span></a>
    <a class="row" href="https://wa.me/${PHONE_WA}?text=${encodeURIComponent(L.hi)}" target="_blank" rel="noopener"><span class="ic">✉</span><span><b>${L.cWA}</b>${L.hi}</span></a>
    <div class="row"><span class="ic">⌂</span><span><b>${L.cAddr}</b>${L.company}<br>${L.addr}</span></div>
   </div>
   <form class="f" id="cf" novalidate>
    <div class="two"><label>${L.cName}<input id="cn" autocomplete="name"><span class="err" id="e1"></span></label><label>${L.cPhone}<input id="cp" type="tel" autocomplete="tel" placeholder="+998"><span class="err" id="e2"></span></label></div>
    <label>${L.cCompany}<input id="cc" autocomplete="organization"></label>
    <div><div style="font-weight:600;font-size:14px;margin-bottom:8px">${L.cType}</div><div class="seltype" id="ct">${['ret','hor','dist','oth'].map((x,i)=>`<button type="button" class="chip" aria-pressed="${i===0}" data-t="${L['t_'+x]}">${L['t_'+x]}</button>`).join('')}</div></div>
    <label>${L.cMsg}<textarea id="cm" rows="4"></textarea></label>
    <button class="btn grn" type="submit">${L.cSend}</button>
   </form></div></div>`;
}

/* ---------- inquiry drawer ---------- */
function waText(extra){
  const L=t();
  const lines=Object.entries(cart).map(([id,n])=>`• ${nm(byId[id])} ${wtxt(byId[id])} — ${n} ${L.boxes}`);
  return [L.order,...lines,extra||''].filter(Boolean).join('\n');
}
function renderCart(){
  const L=t(); const n=Object.values(cart).reduce((a,b)=>a+b,0);
  $('#cartN').textContent=n; $('#cartLbl').textContent=L.inq;
  $('#dTitle').textContent=L.dTitle; $('#dClose').textContent=L.close;
  const items=Object.entries(cart);
  $('#dList').innerHTML=items.length?items.map(([id,q])=>`<div class="li"><div><b>${nm(byId[id])}</b><div class="muted" style="font-size:13px">${wtxt(byId[id])}${byId[id].box?' · '+boxtxt(byId[id]):''}</div></div>
    <div class="qty"><button data-c="${id}" data-d="-1" aria-label="−">−</button><span>${q}</span><button data-c="${id}" data-d="1" aria-label="+">+</button></div></div>`).join(''):`<p class="muted" style="padding:24px 0">${L.empty}</p>`;
  $('#dFoot').innerHTML=items.length?`<a class="btn grn" target="_blank" rel="noopener" href="https://wa.me/${PHONE_WA}?text=${encodeURIComponent(waText())}">${L.sendWA}</a><a class="btn out" href="tel:+998946944499">${L.toCall}</a><button class="btn out sm" id="dClear">${L.clear}</button>`:'';
}
function openDrawer(o){ $('#drawer').classList.toggle('on',o);$('#scrim').classList.toggle('on',o);$('#drawer').setAttribute('aria-hidden',!o); if(o)$('#dClose').focus(); }

/* ---------- router ---------- */
function route(){
  const h=location.hash.replace(/^#\/?/,'').split('/'); const L=t();
  const app=$('#app'); let html,active='home';
  if(h[0]==='catalog'){html=vCatalog(h[1]);active='catalog'}
  else if(h[0]==='item'){html=vItem(h[1]);active='catalog'}
  else if(h[0]==='contact'){html=vContact();active='contact'}
  else html=vHome();
  app.innerHTML=html; window.scrollTo(0,0);
  $('#nav').innerHTML=[['home','#/',L.home],['catalog','#/catalog',L.catalog],['contact','#/contact',L.contact]].map(([k,u,l])=>`<a href="${u}" class="${k===active?'on':''}" ${k===active?'aria-current="page"':''}>${l}</a>`).join('');
  const s=$('#sortSel'); if(s)s.value=sortBy;
  document.title='SAXIY — '+(h[0]==='item'&&byId[h[1]]?nm(byId[h[1]]):h[0]==='catalog'?L.catalog:h[0]==='contact'?L.contact:'Premium Snacks');
  $('#fAbout').textContent=L.fAbout;$('#fAddr').innerHTML=`${L.company}<br>${L.addr}`;$('#fShop').textContent=L.fShop;$('#fContact').textContent=L.fContact;
  $('#fLinks').innerHTML=CATS.map(k=>`<a href="#/catalog/${k}">${L.cats[k]}</a>`).join('');
  document.documentElement.lang=lang;
  document.querySelectorAll('[data-lang]').forEach(b=>b.setAttribute('aria-pressed',b.dataset.lang===lang));
  renderCart();
}
window.addEventListener('hashchange',()=>{filterW='all';route()});

/* ---------- events ---------- */
document.addEventListener('click',e=>{
  const el=e.target.closest('button,[data-lang]'); if(!el)return;
  if(el.dataset.lang){lang=el.dataset.lang;try{localStorage.setItem('saxiy-lang',lang)}catch(e){}route();return}
  if(el.dataset.add){addCart(el.dataset.add);toast(t().added);return}
  if(el.dataset.open){openDrawer(true);return}
  if(el.id==='cartBtn'){openDrawer(true);return}
  if(el.id==='dClose'){openDrawer(false);return}
  if(el.id==='dClear'){for(const k in cart)delete cart[k];renderCart();return}
  if(el.dataset.c){addCart(el.dataset.c,+el.dataset.d);return}
  if(el.dataset.go){location.hash=el.dataset.go;return}
  if(el.dataset.w){filterW=el.dataset.w;route();return}
  if(el.dataset.q){const v=$('#iqv');v.textContent=Math.max(1,+v.textContent+ +el.dataset.q);return}
  if(el.id==='iadd'){addCart(el.dataset.id,+$('#iqv').textContent);toast(t().added);return}
  if(el.dataset.t){document.querySelectorAll('#ct .chip').forEach(c=>c.setAttribute('aria-pressed',c===el));return}
});
$('#scrim').addEventListener('click',()=>openDrawer(false));
document.addEventListener('keydown',e=>{if(e.key==='Escape')openDrawer(false)});
document.addEventListener('change',e=>{if(e.target.id==='sortSel'){sortBy=e.target.value;route()}});
document.addEventListener('submit',e=>{
  if(e.target.id!=='cf')return; e.preventDefault(); const L=t();
  const n=$('#cn').value.trim(),p=$('#cp').value.trim();
  $('#e1').textContent=n?'':L.errName; $('#e2').textContent=p?'':L.errPhone; if(!n||!p)return;
  const type=document.querySelector('#ct .chip[aria-pressed="true"]')?.dataset.t||'';
  const info=`${L.cName}: ${n}\n${L.cPhone}: ${p}\n${$('#cc').value?L.cCompany+': '+$('#cc').value+'\n':''}${type}\n${$('#cm').value}`;
  const txt=Object.keys(cart).length?waText(info):L.hi+'\n'+info;
  window.open('https://wa.me/'+PHONE_WA+'?text='+encodeURIComponent(txt),'_blank','noopener');
});
route();
</script>
@endverbatim
</body>
</html>
