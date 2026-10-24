from html.parser import HTMLParser
from pathlib import Path
from urllib.parse import parse_qsl, unquote, urlencode, urlsplit
import re
import shutil

ROOT = Path(__file__).parent
SOURCE = ROOT / "cloudflare-stage-2026-10-24"
AR_SOURCE = ROOT / "cloudflare-stage-2026-10-22-latest"
DEST = ROOT / "cf-preview-v20"
if DEST.exists():
    raise SystemExit(f"Refusing to overwrite existing package: {DEST}")


class Refs(HTMLParser):
    def __init__(self):
        super().__init__()
        self.refs = []

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        self.refs.extend(attrs[k] for k in ("src", "href", "poster") if attrs.get(k))
        if attrs.get("srcset"):
            self.refs.extend(x.strip().split()[0] for x in attrs["srcset"].split(",") if x.strip())
        if attrs.get("style"):
            self.refs.extend(re.findall(r"url\([\"']?([^\)\"']+)", attrs["style"]))


def local_href(match, lang):
    prefix, value, suffix = match.groups()
    parsed = urlsplit(value)
    if parsed.netloc and parsed.netloc not in ("localhost", "almasaralraed.com", "www.almasaralraed.com"):
        return match.group(0)
    path, query = parsed.path or "/", parsed.query
    query_items = [(key, value) for key, value in parse_qsl(query, keep_blank_values=True) if key != "uabs_lang"]
    if path.startswith("/almasar/"):
        path = path[len("/almasar"):]
    ar_switch = "uabs_lang=ar" in query
    en_switch = "uabs_lang=en" in query
    if lang == "en" and ar_switch:
        path = "/ar/" if path == "/" else "/ar" + path
    elif lang == "ar":
        if not en_switch and not path.startswith("/ar") and (path == "/" or path.startswith(("/shop/", "/product-category/"))):
            path = "/ar/" if path == "/" else "/ar" + path
    query = urlencode(query_items, doseq=True)
    return prefix + path + ("?" + query if query else "") + suffix


def localize(html, lang):
    html = html.replace("?ver=1.8.2", "?ver=1.8.6").replace("?ver=1.8.3", "?ver=1.8.6").replace("?ver=1.8.4", "?ver=1.8.6").replace("?ver=1.8.5", "?ver=1.8.6")
    # A legacy Customizer override was forcing Gadgets into a navy page-wide theme.
    # The current theme stylesheet owns the palette; omit that stale inline override.
    html = re.sub(r'<style\s+id=["\']wp-custom-css["\']\s*>.*?</style>', '', html, flags=re.I | re.S)
    return re.sub(r"(?i)(\bhref\s*=\s*[\"'])([^\"']+)([\"'])", lambda m: local_href(m, lang), html)


DEST.mkdir(parents=True)
pages = {}
for file in SOURCE.rglob("*.html"):
    rel = file.relative_to(SOURCE).as_posix()
    if rel.startswith("category/"):
        continue
    if rel in ("404.html", "index.html", "shop/index.html") or rel.startswith("product-category/"):
        pages[rel] = localize(file.read_text(encoding="utf-8", errors="replace"), "en")

ar_translations = {
    # Preserve Arabic-only storefront copy across the mixed-case strings emitted by WordPress.
    "Two worlds, one point of view · Curated in the UAE": "عالَمان، رؤية واحدة · اختيارات من الإمارات",
    "Two worlds, one point of view": "عالَمان، رؤية واحدة",
    "The Carry Archive": "أرشيف الحقائب",
    "The Gadget Studio": "استوديو الأجهزة",
    "Gadgets & Home": "الأجهزة والمنزل",
    "Gadgets &amp; Home": "الأجهزة والمنزل",
    "Shoulder bags": "حقائب كتف",
    "Crossbody bags": "حقائب كروس بودي",
    "Tote bags": "حقائب يد كبيرة",
    "Small leather goods": "إكسسوارات جلدية صغيرة",
    "Home fragrance": "العطور المنزلية",
    "Air care": "تنقية الهواء",
    "Bottles & hydration": "الأكواب والقوارير",
    "Camp & outdoors": "التخييم والأنشطة الخارجية",
    "Gaming": "الألعاب",
    "Kitchen & dining": "المطبخ وتناول الطعام",
    "Lighting": "الإضاءة",
    "Personal care": "العناية الشخصية",
    "Projectors": "أجهزة العرض",
    "Speakers & audio": "السماعات والصوتيات",
    "Tech & utility": "التقنية والأدوات",
    "Travel essentials": "أساسيات السفر",
    "Price from": "السعر من",
    "Price to": "السعر إلى",
    "A sculptural leather bag in a sunlit atelier": "حقيبة جلدية بتصميم أنيق في أتيليه مضاء بالشمس",
    "A sculptural bag in warm editorial light": "حقيبة بتصميم أنيق في ضوء دافئ",
    "Modern gadgets for sound, home and everyday rituals": "أجهزة عصرية للصوت والمنزل وتفاصيل الحياة اليومية",
    "Designed for the way you live": "مصممة لترافق تفاصيل يومك",
    "Form & movement": "الشكل والحركة",
    "Form · material · movement": "الشكل · الخامة · الحركة",
    "Enter the bag collection": "اكتشف مجموعة الحقائب",
    "Enter the gadgets collection": "اكتشف مجموعة الأجهزة",
    "A house of two distinct worlds": "دار لعالمين مختلفين",
    "Dubai": "دبي",
    "Shop all products": "تسوّق جميع المنتجات",
    "Two distinct collections": "تشكيلتان متميزتان",
    "Choose your world to browse.": "اختر العالم الذي ترغب باستكشافه.",
    "Objects for living": "أغراض للحياة اليومية",
    "Accessories": "الإكسسوارات",
    "Clutches": "حقائب يد صغيرة",
    "Crossbody Bags": "حقائب كروس بودي",
    "Crossbody bags": "حقائب كروس بودي",
    "Handbags": "حقائب يد",
    "Mini Bags": "حقائب صغيرة",
    "Mini bags": "حقائب صغيرة",
    "Shoulder Bags": "حقائب كتف",
    "Small Leather Goods": "إكسسوارات جلدية صغيرة",
    "Tote Bags": "حقائب يد كبيرة",
    "Travel Organisers": "منظمات السفر",
    "All sizes": "كل المقاسات",
    "Size": "المقاس",
    "GADGETS & HOME": "الأجهزة والمنزل",
    "Gadgets & Home": "الأجهزة والمنزل",
    "Gadgets &amp; Home": "الأجهزة والمنزل",
    "Home": "المنزل",
    "HOME": "المنزل",
    "Bag archive": "أرشيف الحقائب",
    "TWO WORLDS, ONE POINT OF VIEW · CURATED IN THE UAE": "عالَمان برؤية واحدة · اختيارات من الإمارات",
    "A quieter kind of statement.": "أناقة هادئة تعبّر عنك.",
    "Shapes made for the everyday.": "تصاميم ترافق تفاصيل يومك.",
    "Useful, with a point of view.": "اختيارات عملية برؤية مميزة.",
    "Sound · ritual · home · travel": "الصوت · الطقوس اليومية · المنزل · السفر",
    "Form & material": "الشكل والخامة",
    "Form &amp; material": "الشكل والخامة",
    "Refine your selection": "تصفّح حسب تفضيلاتك",
    "Gadgets & المنزل": "الأجهزة والمنزل",
    "الأجهزة & المنزل": "الأجهزة والمنزل",
    "FORM &amp; MOVEMENT": "الشكل والحركة",
    "FORM · MATERIAL · MOVEMENT": "الشكل · الخامة · الحركة",
    "Gadgets & home": "الأجهزة والمنزل",
    "Gadgets &amp; home": "الأجهزة والمنزل",
    "Gadgets &amp; Home": "الأجهزة والمنزل",
    "Mini bags": "حقائب صغيرة",
    "Evening & clutches": "حقائب السهرة والحقائب الصغيرة",
    "Evening &amp; clutches": "حقائب السهرة والحقائب الصغيرة",
    "Drinkware": "أكواب وقوارير",
    "Outdoor & travel": "الأنشطة الخارجية والسفر",
    "Outdoor &amp; travel": "الأنشطة الخارجية والسفر",
    "Explore all bags": "استكشف جميع الحقائب",
    "Explore all gadgets": "استكشف جميع الأجهزة",
    "ALMASAR / 01": "المسار / 01",
    "ALMASAR / 02": "المسار / 02",
    "01 / THE CARRY ARCHIVE": "01 / أرشيف الحقائب",
    "02 / THE GADGET STUDIO": "02 / استوديو الأجهزة",
    "Tech & Utility": "التقنية والأدوات",
    "Tech &amp; Utility": "التقنية والأدوات",
    "Price from · AED": "السعر من · درهم",
    "Price to · AED": "السعر إلى · درهم",
    "PRICE FROM/TO · AED": "السعر من / إلى · درهم",
    "Price from/to · AED": "السعر من / إلى · درهم",
    "ENTER THE BAG COLLECTION": "اكتشف مجموعة الحقائب",
    "ENTER THE GADGETS COLLECTION": "اكتشف مجموعة الأجهزة",
    "A sculptural leather bag in a sunlit atelier": "حقيبة جلدية بتصميم أنيق في أتيليه مضاء بالشمس",
    "A HOUSE OF TWO DISTINCT WORLDS": "دار لعالمين مختلفين",
    "A house of two distinct worlds": "دار لعالمين مختلفين",
    "DUBAI": "دبي",
    "DUBAI · UAE": "دبي · الإمارات",
    "Curated in the UAE": "اختيارات من الإمارات",
    "The Carry Archive": "أرشيف الحقائب",
    "The Gadget Studio": "استوديو الأجهزة",
    "The Carry Archive for considered bags.": "أرشيف الحقائب لتصاميم منتقاة.",
    "The Gadget Studio for thoughtful finds across home, sound and travel.": "استوديو الأجهزة لاكتشافات مدروسة للمنزل والصوت والسفر.",
    "TWO WORLDS, ONE POINT OF VIEW · CURATED IN THE UAE": "عالَمان برؤية واحدة · اختيارات من الإمارات",
    "TWO WORLDS, ONE POINT OF VIEW": "عالَمان برؤية واحدة",
    "CURATED IN THE UAE": "اختيارات من الإمارات",
    "ALMASAR AL RAED · DUBAI": "المسار الرائد · دبي",
    "Two worlds. One point of view.": "عالَمان، ورؤية واحدة.",
    "Choose a collection to begin.": "اختر التشكيلة للبدء.",
    "Choose a collection": "اختر التشكيلة",
    "Choose a department": "اختر قسماً",
    "01 / THE CARRY ARCHIVE": "01 / أرشيف الحقائب",
    "THE CARRY ARCHIVE": "أرشيف الحقائب",
    "FORM · MATERIAL · MOVEMENT": "الشكل · الخامة · الحركة",
    "FORM & MOVEMENT": "الشكل والحركة",
    "Bags": "الحقائب",
    "Almasar": "المسار",
    "Al Raed": "الرائد",
    "Almasar Al Raed": "المسار الرائد",
    "Carry what matters. Keep the rest simple.": "احمل ما يهمك، واترك الباقي ببساطة.",
    "ENTER THE BAG COLLECTION": "اكتشف مجموعة الحقائب",
    "02 / THE GADGET STUDIO": "02 / استوديو الأجهزة",
    "THE GADGET STUDIO": "استوديو الأجهزة",
    "GADGETS & HOME": "الأجهزة والمنزل",
    "GADGETS": "الأجهزة",
    "HOME": "المنزل",
    "DESIGNED FOR THE WAY YOU LIVE": "مصممة لترافق تفاصيل يومك",
    "Gadgets": "الأجهزة والإكسسوارات",
    "Useful discoveries for home, travel, sound and everyday rituals.": "اكتشافات عملية للمنزل والسفر والصوت وتفاصيل الحياة اليومية.",
    "ENTER THE GADGETS COLLECTION": "اكتشف مجموعة الأجهزة",
    "ENTER THE GADGET COLLECTION": "اكتشف مجموعة الأجهزة",
    "A HOUSE OF TWO DISTINCT WORLDS": "دار لعالمين مختلفين",
    "Choose your point of view.": "اختر عالمك.",
    "The Carry Archive for considered bags. The Gadget Studio for thoughtful finds across home, sound and travel.": "أرشيف الحقائب لتصاميم منتقاة، واستوديو الأجهزة لاكتشافات مدروسة للمنزل والصوت والسفر.",
    "Store services": "خدمات المتجر",
    "Curated in the UAE": "اختيارات من الإمارات",
    "Delivery across all seven emirates": "توصيل إلى الإمارات السبع",
    "Personal assistance on WhatsApp": "مساعدة شخصية عبر واتساب",
    "Thoughtful finds for everyday and occasion.": "اختيارات أنيقة لكل يوم ومناسبة.",
    "CHOOSE A COLLECTION": "اختر التشكيلة",
    "YOUR BAG": "سلتك",
    "WHATSAPP": "واتساب",
    "INSTAGRAM": "إنستغرام",
    "Search products": "ابحثي عن المنتجات",
    "Search Products": "ابحثي عن المنتجات",
    "Search the collection": "ابحثي في التشكيلة",
    "Search": "بحث",
    "Account": "حسابي",
    "WhatsApp": "واتساب",
    "Instagram": "إنستغرام",
    "Bag": "السلة",
    "Explore collections": "استكشف التشكيلات",
    "Everyday Objects": "الأجهزة والإكسسوارات",
    "Category": "الفئة",
    "CATEGORY": "الفئة",
    "Color": "اللون",
    "COLOR": "اللون",
    "Brand": "العلامة التجارية",
    "BRAND": "العلامة التجارية",
    "PRICE FROM · AED": "السعر من · درهم",
    "PRICE TO · AED": "السعر إلى · درهم",
    "PRICE FROM · AED": "السعر من · درهم",
    "PRICE TO · AED": "السعر إلى · درهم",
    "PRICE FROM": "السعر من",
    "PRICE TO": "السعر إلى",
    "AED": "درهم",
    "Apply": "تطبيق",
    "APPLY": "تطبيق",
    "Clear": "مسح",
    "All categories": "كل الفئات",
    "ALL CATEGORIES": "كل الفئات",
    "Air Care": "تنقية الهواء",
    "Bottles & Hydration": "الأكواب والقوارير",
    "Camp & Outdoors": "التخييم والأنشطة الخارجية",
    "Gaming": "الألعاب",
    "Home Fragrance": "العطور المنزلية",
    "Kitchen & Dining": "المطبخ والقهوة",
    "Lighting": "الإضاءة",
    "Personal Care": "العناية الشخصية",
    "Projectors": "أجهزة العرض",
    "Speakers & Audio": "السماعات والصوتيات",
    "Tech & Utility": "التقنية والأدوات",
    "Travel Essentials": "السفر والأدوات اليومية",
    "All colors": "كل الألوان",
    "ALL COLORS": "كل الألوان",
    "All brands": "كل العلامات التجارية",
    "ALL BRANDS": "كل العلامات التجارية",
    "Bottles &amp; Hydration": "الأكواب والقوارير",
    "Camp &amp; Outdoors": "التخييم والأنشطة الخارجية",
    "Kitchen &amp; Dining": "المطبخ والقهوة",
    "Kitchen &amp; coffee": "المطبخ والقهوة",
    "Kitchen & coffee": "المطبخ والقهوة",
    "Speakers &amp; Audio": "السماعات والصوتيات",
    "Speakers &amp; audio": "السماعات والصوتيات",
    "A sculptural bag in warm editorial light": "حقيبة بتصميم أنيق في ضوء دافئ",
    "Modern gadgets for sound, home and everyday rituals": "أجهزة عصرية للصوت والمنزل وتفاصيل الحياة اليومية",
    "Your bag": "سلتك",
    "Your Bag": "سلتك",
    "Add the sample listings to your bag to review them. Prices and details are illustrative; payment is unavailable in this preview.": "أضف العناصر التجريبية إلى سلتك لمراجعتها. الأسعار والمواصفات افتراضية، والدفع غير متاح في هذه النسخة.",
    "Illustrative preview subtotal": "المجموع التقديري للعرض",
    "Illustrative preview · sample prices and details, not live stock": "عرض توضيحي · الأسعار والمواصفات افتراضية وليست مخزوناً للبيع",
    "SAMPLE · NOT FOR SALE": "عينة توضيحية · غير متاحة للبيع",
    "A sample concept to preview the product listing and bag. Details and price are illustrative and do not represent live stock.": "تصميم تجريبي لعرض شكل المنتج في المتجر. المواصفات والسعر افتراضية، ولا تمثل منتجاً أو مخزوناً متاحاً للبيع.",
    "Finish: warm sand · Size: medium · sample material": "اللون: رملي · المقاس: متوسط · خامة تجريبية",
    "Finish: deep navy · sample home and drinkware set": "اللون: أزرق داكن · طقم تجريبي لأدوات المنزل",
    "Checkout and order submission are unavailable in this preview.": "الدفع وإرسال الطلب غير متاحين في هذه النسخة التجريبية.",
    "Checkout unavailable": "الدفع غير متاح",
    "Clear bag": "إفراغ السلة",
    "Explore bags": "تصفّح الحقائب",
    "Explore gadgets": "تصفّح الأجهزة",
    "Your bag is empty for now.": "سلتك فارغة حالياً.",
    "Bag summary": "ملخص السلة",
    "Preview listing": "قائمة تجريبية",
    "Add sample listings to your bag to review them here. Prices and payment are unavailable in this preview.": "أضف العناصر التجريبية إلى سلتك لمراجعتها هنا. الأسعار والدفع غير متاحين في هذه النسخة.",
    "Your bag is empty for now.": "سلتك فارغة حالياً.",
    "Explore bags": "تصفّح الحقائب",
    "Explore gadgets": "تصفّح الأجهزة",
    "Bag summary": "ملخص السلة",
    "Checkout and order submission are unavailable in this preview.": "الدفع وإرسال الطلب غير متاحين في هذه النسخة التجريبية.",
    "Checkout unavailable": "الدفع غير متاح",
    "Clear bag": "إفراغ السلة",
    "No products were found matching your selection.": "لم يتم العثور على منتجات تطابق اختيارك.",
    "© 2026 Almasar Al Raed": "© 2026 المسار الرائد",
    "BAGS": "الحقائب",
    "New Gadgets": "أحدث الأجهزة",
    "NEW GADGETS": "أحدث الأجهزة",
    "Bag 0": "السلة 0",
    "الحقائب – Almasar": "الحقائب – المسار الرائد",
}


def arabic_page(rel, source_rel, title=None):
    html = pages[source_rel]
    for english, arabic in sorted(ar_translations.items(), key=lambda item: len(item[0]), reverse=True):
        html = html.replace(english, arabic)
    html = html.replace('<html lang="en" dir="ltr">', '<html lang="ar" dir="rtl">')
    html = re.sub(r'(<body\b[^>]*class=["\'])([^"\']*)(["\'])',
                  lambda m: m.group(1) + re.sub(r"\buabs-lang-en\b", "uabs-lang-ar", m.group(2)) + " rtl" + m.group(3),
                  html, count=1)
    if title:
        html = re.sub(r"(<title>).*?(</title>)", lambda m: m.group(1) + title + " – المسار الرائد" + m.group(2), html, count=1, flags=re.S)
    # Point static internal navigation to the Arabic route set while keeping language switch links English.
    html = localize(html, "ar")
    def fix_language_switcher(tag_match):
        tag = tag_match.group(0)
        if re.search(r'\blang=["\']en["\']', tag):
            tag = re.sub(r'(?i)(href=["\'])/ar(/[^"\']*)', r'\1\2', tag)
        return tag
    html = re.sub(r'<a\b[^>]*>', fix_language_switcher, html, flags=re.I)
    return html


for rel, source_rel, title in (
    ("ar/index.html", "index.html", None),
    ("ar/shop/index.html", "shop/index.html", "المتجر"),
    ("ar/product-category/bags/index.html", "product-category/bags/index.html", "الحقائب"),
    ("ar/product-category/everyday-objects/index.html", "product-category/everyday-objects/index.html", "الأجهزة والإكسسوارات"),
):
    pages[rel] = arabic_page(rel, source_rel, title)

translations = {
    "bags/shoulder-bags": "حقائب كتف",
    "bags/crossbody-bags": "حقائب كروس بودي",
    "bags/tote-bags": "حقائب يد كبيرة",
    "bags/small-leather-goods": "إكسسوارات جلدية صغيرة",
    "everyday-objects/kitchen-and-dining": "المطبخ وتناول الطعام",
    "everyday-objects/camp-and-outdoors": "التخييم والأنشطة الخارجية",
    "everyday-objects/bottles-and-hydration": "الزجاجات والترطيب",
    "everyday-objects/travel-essentials": "أساسيات السفر",
    "everyday-objects/tech-and-utility": "التقنية والأدوات العملية",
}
for slug, title in translations.items():
    parent = "bags" if slug.startswith("bags/") else "everyday-objects"
    html = pages[f"ar/product-category/{parent}/index.html"]
    html = re.sub(r"(<title>).*?(</title>)", lambda m: m.group(1) + title + " – المسار الرائد" + m.group(2), html, count=1, flags=re.S)
    html = html.replace("الحقائب</h1>", title + "</h1>").replace("الأجهزة والإكسسوارات</h1>", title + "</h1>")
    pages[f"ar/product-category/{slug}/index.html"] = html


def demo_product_card(department, lang):
    is_bags = department == "bags"
    if lang == "ar":
        title = "حقيبة حمل بتصميم منحوت" if is_bags else "طقم الطقوس اليومية"
        alt = "صورة توضيحية لحقيبة بلون محايد دافئ" if is_bags else "صورة توضيحية لأدوات القهوة والشرب في المطبخ"
        note = "عرض توضيحي · الأسعار والمواصفات افتراضية وليست مخزوناً للبيع"
        badge = "عينة توضيحية · غير متاحة للبيع"
        description = "تصميم تجريبي لعرض شكل المنتج في المتجر. المواصفات والسعر افتراضية، ولا تمثل منتجاً أو مخزوناً متاحاً للبيع."
        details = "اللون: رملي · المقاس: متوسط · خامة تجريبية" if is_bags else "اللون: أزرق داكن · طقم تجريبي لأدوات المنزل"
        price = "329" if is_bags else "249"
        section_label = "قائمة منتجات تجريبية"
        add_label = "أضف إلى السلة"
    else:
        title = "The Sculpted Carry Tote" if is_bags else "The Daily Ritual Set"
        alt = "Sample concept handbag in a warm neutral tone" if is_bags else "Sample drinkware and coffee accessories arranged in a kitchen"
        note = "Illustrative preview · sample prices and details, not live stock"
        badge = "SAMPLE · NOT FOR SALE"
        description = "A sample concept to preview the product listing and bag. Details and price are illustrative and do not represent live stock."
        details = "Finish: warm sand · Size: medium · sample material" if is_bags else "Finish: deep navy · sample home and drinkware set"
        price = "329" if is_bags else "249"
        section_label = "Sample product listing"
        add_label = "Add to bag"
    image = "category-handbags.jpg" if is_bags else "category-kitchen-ritual.jpg"
    sequence = "01" if is_bags else "02"
    product_id = "sample-bag" if is_bags else "sample-gadget"
    product_href = ("/ar" if lang == "ar" else "") + "/product/" + product_id + "/"
    return (
        f'<section class="uabb-demo-products" aria-label="{section_label}">'
        f'<p class="uabb-demo-products-note">{note}</p>'
        '<ul class="products columns-3 uabb-demo-products-grid">'
        '<li class="product uabb-demo-product"><div class="uabb-demo-product-image">'
        f'<a href="{product_href}" aria-label="{title}"><img src="/wp-content/themes/uae-bag-boutique/assets/images/{image}" alt="{alt}" loading="lazy" width="960" height="640"></a>'
        f'<span class="uabb-demo-product-badge">{badge}</span></div><div class="uabb-demo-product-copy">'
        f'<p class="uabb-demo-product-brand">ALMASAR / {sequence}</p><h2><a href="{product_href}">{title}</a></h2>'
        f'<p class="uabb-demo-product-price">AED {price}.00</p><p class="uabb-demo-product-details">{details}</p><p>{description}</p>'
        f'<button class="uabb-preview-add" type="button" data-preview-add="{product_id}">{add_label}</button>'
        '</div></li></ul></section>'
    )


def add_demo_product(rel, department, lang):
    html = pages[rel]
    empty_state = re.compile(
        r'<div class="woocommerce-no-products-found">\s*'
        r'<div class="woocommerce-info" role="status">.*?</div>\s*</div>',
        re.S,
    )
    updated, count = empty_state.subn(demo_product_card(department, lang), html, count=1)
    if count != 1:
        raise SystemExit(f"Could not add sample catalog item to {rel}")
    pages[rel] = updated


for rel, department, lang in (
    ("product-category/bags/index.html", "bags", "en"),
    ("product-category/everyday-objects/index.html", "gadgets", "en"),
    ("ar/product-category/bags/index.html", "bags", "ar"),
    ("ar/product-category/everyday-objects/index.html", "gadgets", "ar"),
):
    add_demo_product(rel, department, lang)


def make_demo_detail(department, lang):
    is_bags = department == "bags"
    if lang == "ar":
        title = "حقيبة حمل بتصميم منحوت" if is_bags else "طقم الطقوس اليومية"
        eyebrow = "الحقائب · عينة توضيحية" if is_bags else "الأجهزة والمنزل · عينة توضيحية"
        image = "category-handbags.jpg" if is_bags else "category-kitchen-ritual.jpg"
        alt = "صورة توضيحية لحقيبة بلون محايد دافئ" if is_bags else "صورة توضيحية لأدوات القهوة والشرب في المطبخ"
        finish = "اللون: رملي · المقاس: متوسط · خامة تجريبية" if is_bags else "اللون: أزرق داكن · طقم تجريبي لأدوات المنزل"
        price = "329" if is_bags else "249"
        paragraph = "هذه صفحة تجريبية لمراجعة شكل المنتج وتدفق السلة. السعر والمواصفات افتراضية ولا تمثل مخزوناً للبيع. الدفع وإرسال الطلب غير متاحين في هذه النسخة."
        add_label, back_label = "أضف إلى السلة", "العودة إلى المنتجات"
        base = "/ar"
    else:
        title = "The Sculpted Carry Tote" if is_bags else "The Daily Ritual Set"
        eyebrow = "BAGS · SAMPLE PREVIEW" if is_bags else "GADGETS & HOME · SAMPLE PREVIEW"
        image = "category-handbags.jpg" if is_bags else "category-kitchen-ritual.jpg"
        alt = "Sample concept handbag in a warm neutral tone" if is_bags else "Sample drinkware and coffee accessories arranged in a kitchen"
        finish = "Finish: warm sand · Size: medium · sample material" if is_bags else "Finish: deep navy · sample home and drinkware set"
        price = "329" if is_bags else "249"
        paragraph = "Review this sample product page and add-to-bag flow. The price and specifications are illustrative and do not represent live stock. Checkout and order submission are unavailable in this preview."
        add_label, back_label = "Add to bag", "Back to collection"
        base = ""
    source = pages[("ar/" if lang == "ar" else "") + "shop/index.html"]
    product_id = "sample-bag" if is_bags else "sample-gadget"
    href = base + ("/product-category/bags/" if is_bags else "/product-category/everyday-objects/")
    main = f'''<main class="uabb-main uabb-product-preview-page"><div class="uabb-content-wrap uabb-woocommerce-wrap">
  <nav class="uabb-product-preview-crumb"><a href="{href}">{back_label}</a></nav>
  <section class="uabb-product-preview" aria-labelledby="uabb-product-preview-title">
    <div class="uabb-product-preview-image"><img src="/wp-content/themes/uae-bag-boutique/assets/images/{image}" alt="{alt}" width="960" height="640"></div>
    <div class="uabb-product-preview-copy"><p class="uabb-eyebrow">{eyebrow}</p><p class="uabb-demo-product-brand">ALMASAR / {'01' if is_bags else '02'}</p><h1 id="uabb-product-preview-title">{title}</h1>
      <p class="uabb-product-preview-price">AED {price}.00</p><p class="uabb-product-preview-details">{finish}</p><p class="uabb-product-preview-note">{'عرض توضيحي · الأسعار والمواصفات افتراضية وليست مخزوناً للبيع' if lang == 'ar' else 'Illustrative preview · sample price and details, not live stock'}</p><p>{paragraph}</p>
      <button class="uabb-preview-add" type="button" data-preview-add="{product_id}">{add_label}</button>
    </div>
  </section>
</div></main>'''
    html = re.sub(r'<main class="uabb-main">.*?</main>', main, source, count=1, flags=re.S)
    if html == source:
        raise SystemExit("Could not create sample product detail page")
    html = re.sub(r"<title>.*?</title>", lambda m: "<title>" + title + " · Almasar Al Raed</title>", html, count=1, flags=re.S)
    return html


for lang in ("en", "ar"):
    prefix = "ar/" if lang == "ar" else ""
    pages[prefix + "product/sample-bag/index.html"] = make_demo_detail("bags", lang)
    pages[prefix + "product/sample-gadget/index.html"] = make_demo_detail("gadgets", lang)


def make_cart_page(source_rel, lang):
    html = pages[source_rel]
    if lang == "ar":
        title, eyebrow, intro = "سلتك", "قائمة تجريبية", "أضف العناصر إلى السلة لمراجعتها. الأسعار والمواصفات افتراضية، والدفع غير متاح في هذه النسخة."
        empty, browse_bags, browse_gadgets, payment_note, checkout = "سلتك فارغة حالياً", "تصفّح الحقائب", "تصفّح الأجهزة", "الدفع وإرسال الطلب غير متاحين في هذه النسخة التجريبية.", "الدفع غير متاح"
        summary, subtotal, remove_all = "ملخص السلة", "المجموع التقديري للعرض", "إفراغ السلة"
    else:
        title, eyebrow, intro = "Your bag", "Preview listing", "Add the sample listings to your bag to review them. Prices and details are illustrative; payment is unavailable in this preview."
        empty, browse_bags, browse_gadgets, payment_note, checkout = "Your bag is empty for now.", "Explore bags", "Explore gadgets", "Checkout and order submission are unavailable in this preview.", "Checkout unavailable"
        summary, subtotal, remove_all = "Bag summary", "Illustrative preview subtotal", "Clear bag"
    content = f'''<main class="uabb-main uabb-cart-preview-page">
<div class="uabb-content-wrap uabb-woocommerce-wrap">
  <section class="uabb-preview-cart" aria-labelledby="uabb-preview-cart-title" data-preview-cart>
    <header class="uabb-preview-cart-heading"><p class="uabb-eyebrow">{eyebrow}</p><h1 id="uabb-preview-cart-title">{title}</h1><p>{intro}</p></header>
    <div class="uabb-preview-cart-layout">
      <div class="uabb-preview-cart-items" data-preview-cart-items aria-live="polite"><p class="uabb-preview-cart-empty">{empty}</p><nav class="uabb-preview-cart-browse"><a href="/product-category/bags/">{browse_bags}</a><a href="/product-category/everyday-objects/">{browse_gadgets}</a></nav></div>
      <aside class="uabb-preview-cart-summary"><h2>{summary}</h2><p class="uabb-preview-cart-count" data-preview-cart-summary></p><div class="uabb-preview-subtotal"><span>{subtotal}</span><strong data-preview-cart-total>AED 0.00</strong></div><p class="uabb-preview-payment-note">{payment_note}</p><button class="uabb-preview-checkout" type="button" disabled aria-disabled="true">{checkout}</button><button class="uabb-preview-clear" type="button" data-preview-clear>{remove_all}</button></aside>
    </div>
  </section>
</div>
</main>'''
    html, count = re.subn(r'<main class="uabb-main">.*?</main>', content, html, count=1, flags=re.S)
    if count != 1:
        raise SystemExit(f"Could not prepare cart page from {source_rel}")
    return html


pages["cart/index.html"] = make_cart_page("shop/index.html", "en")
pages["ar/cart/index.html"] = arabic_page("ar/cart/index.html", "cart/index.html", "سلتك")
def localize_ar_cart_link(tag_match):
    tag = tag_match.group(0)
    if re.search(r'\blang=["\']en["\']', tag, re.I):
        return tag
    tag = re.sub(r'(?i)(href=["\'])/cart/', r'\1/ar/cart/', tag)
    return tag
pages["ar/cart/index.html"] = re.sub(r'<a\b[^>]*>', localize_ar_cart_link, pages["ar/cart/index.html"], flags=re.I)
for rel, html in list(pages.items()):
    if rel.endswith(".html"):
        script = '<script defer src="/wp-content/themes/uae-bag-boutique/assets/preview-bag.js?ver=1"></script>'
        if "preview-bag.js" not in html:
            html = html.replace("</body>", script + "</body>")
        pages[rel] = html

for rel, html in pages.items():
    if rel.startswith("ar/") and rel.endswith("index.html"):
        route = "/" + rel[:-len("index.html")]
        english_route = "/" + route.removeprefix("/ar/")
        if route == "/ar/":
            english_route = "/"
        html = re.sub(r'(<link\s+rel=["\']canonical["\']\s+href=["\'])[^"\']*', lambda m: m.group(1) + route, html, count=1, flags=re.I)
        def fix_hreflang(match):
            tag = match.group(0)
            if re.search(r'hreflang=["\'](?:en-AE|x-default)["\']', tag, re.I):
                return re.sub(r'(?i)(href=["\'])[^"\']*', lambda href: href.group(1) + english_route, tag, count=1)
            if re.search(r'hreflang=["\']ar-AE["\']', tag, re.I):
                return re.sub(r'(?i)(href=["\'])[^"\']*', lambda href: href.group(1) + route, tag, count=1)
            return tag
        html = re.sub(r'<link\b[^>]*rel=["\']alternate["\'][^>]*>', fix_hreflang, html, flags=re.I)
        pages[rel] = html
    dest = DEST / rel
    dest.parent.mkdir(parents=True, exist_ok=True)
    dest.write_text(html, encoding="utf-8")

# Dependency closure from the selected pages only; never include PHP or vendor source.
allowed = {".css", ".js", ".mjs", ".svg", ".png", ".jpg", ".jpeg", ".webp", ".gif", ".avif", ".woff", ".woff2", ".ttf", ".otf", ".ico", ".json", ".xml", ".txt"}
url_pattern = re.compile(r"url\([\"']?([^\)\"']+)", re.I)
import_pattern = re.compile(r"@import\s+[\"']([^\"']+)", re.I)


def resolve(ref, relative_to):
    ref = ref.strip().strip("\"'")
    if not ref or ref.startswith(("data:", "blob:", "javascript:", "#", "mailto:", "tel:")):
        return None
    parsed = urlsplit(ref)
    if parsed.netloc and parsed.netloc not in ("localhost", "almasaralraed.com", "www.almasaralraed.com"):
        return None
    path = unquote(parsed.path)
    if not path:
        return None
    if path.startswith("/almasar/"):
        path = path[len("/almasar"):]
    rel = Path(path.lstrip("/")) if path.startswith("/") else Path(relative_to).parent / path
    rel = Path(*[part for part in rel.parts if part not in ("", ".")])
    if ".." in rel.parts or rel.suffix.lower() not in allowed:
        return None
    return rel.as_posix()


seen, queue = set(), [(rel, html) for rel, html in pages.items()]
while queue:
    rel, body = queue.pop(0)
    refs = []
    if rel.lower().endswith(".html"):
        parser = Refs(); parser.feed(body); refs.extend(parser.refs)
    if rel.lower().endswith(".css"):
        refs.extend(url_pattern.findall(body)); refs.extend(import_pattern.findall(body))
    for ref in refs:
        asset = resolve(ref, rel)
        if not asset or asset in seen:
            continue
        source = SOURCE / asset
        if not source.is_file():
            source = AR_SOURCE / asset
        if not source.is_file():
            continue
        seen.add(asset)
        target = DEST / asset
        target.parent.mkdir(parents=True, exist_ok=True)
        try:
            shutil.copy2(source, target)
        except OSError as error:
            print(f"Copy failed for {source} -> {target}: {error}")
            raise
        if target.suffix.lower() == ".css":
            queue.append((asset, target.read_text(encoding="utf-8", errors="replace")))

# Keep the deployable preview in sync with the checked-in theme design system.
polish_css = ROOT / "wordpress" / "themes" / "uae-bag-boutique" / "assets" / "polish.css"
polish_target = DEST / "wp-content" / "themes" / "uae-bag-boutique" / "assets" / "polish.css"
polish_target.parent.mkdir(parents=True, exist_ok=True)
shutil.copy2(polish_css, polish_target)
preview_bag_js = ROOT / "wordpress" / "themes" / "uae-bag-boutique" / "assets" / "preview-bag.js"
preview_bag_target = DEST / "wp-content" / "themes" / "uae-bag-boutique" / "assets" / "preview-bag.js"
preview_bag_target.parent.mkdir(parents=True, exist_ok=True)
shutil.copy2(preview_bag_js, preview_bag_target)
polish_css = ROOT / "wordpress" / "themes" / "uae-bag-boutique" / "assets" / "polish.css"
polish_target = DEST / "wp-content" / "themes" / "uae-bag-boutique" / "assets" / "polish.css"
polish_target.parent.mkdir(parents=True, exist_ok=True)
shutil.copy2(polish_css, polish_target)

(DEST / "_headers").write_text(
    "/*\n  X-Content-Type-Options: nosniff\n  X-Frame-Options: DENY\n"
    "  Referrer-Policy: strict-origin-when-cross-origin\n"
    "  Permissions-Policy: camera=(), microphone=(), geolocation=()\n",
    encoding="utf-8",
)
(DEST / "robots.txt").write_text(
    "User-agent: *\nAllow: /\nSitemap: https://almasaralraed.com/sitemap.xml\n", encoding="utf-8"
)
urls = ["https://almasaralraed.com/", "https://almasaralraed.com/ar/"]
for rel in pages:
    if rel.endswith("/index.html") and rel != "ar/index.html":
        urls.append("https://almasaralraed.com/" + rel[:-len("index.html")])
sitemap = ('<?xml version="1.0" encoding="UTF-8"?>\n'
           '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n'
           + "".join(f"  <url><loc>{url}</loc></url>\n" for url in dict.fromkeys(urls))
           + "</urlset>\n")
(DEST / "sitemap.xml").write_text(sitemap, encoding="utf-8")

files = [p for p in DEST.rglob("*") if p.is_file()]
persian = sum(bool(re.search("[پچژگک]", p.read_text(encoding="utf-8", errors="ignore"))) for p in DEST.rglob("*.html"))
print(f"Pages: {len(pages)} | Assets: {len(seen)} | Total: {len(files)} | Size: {sum(p.stat().st_size for p in files)/1048576:.2f} MiB")
print(f"PHP source files: {sum(p.suffix.lower()=='.php' for p in files)} | HTML with Persian-specific letters: {persian}")
