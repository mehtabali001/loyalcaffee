/**
 * Schema for every CMS resource. The generic list + form pages render from this, so adding a
 * field is a one-line change here plus the matching validation rule in the Laravel controller.
 *
 * Field types: trans | transText | text | textarea | number | select | remote | tags | image
 *              | toggle | date | datetime | url
 * `show(form)` hides a field unless it applies (used by homepage sections).
 */

const MENU_TAGS = ['signature', 'new', 'popular', 'vegetarian', 'vegan', 'spicy', 'sharing'];
const GALLERY_CATEGORIES = ['interior', 'shisha', 'drinks', 'food', 'match-nights'];
const CATEGORY_ICONS = ['coffee', 'shisha', 'plate', 'juice', 'glass', 'dessert', 'leaf', 'sun', 'moon'];
const SECTION_TYPES = ['hero', 'story', 'day_night', 'featured_menu', 'matches', 'offers', 'gallery', 'testimonials', 'visit'];

const opts = (list) => list.map((v) => ({ value: v, label: v.replace(/[_-]/g, ' ') }));
const is = (...types) => (f) => types.includes(f.type);
const active = { key: 'is_active', type: 'toggle', label: 'Visible on website', default: true };

export const resources = {
    'menu-categories': {
        title: 'Menu categories',
        singular: 'category',
        sortable: true,
        columns: [
            { key: 'name', type: 'trans', label: 'Name' },
            { key: 'items_count', label: 'Items' },
        ],
        fields: [
            { key: 'name', type: 'trans', label: 'Name', required: true },
            { key: 'description', type: 'transText', label: 'Short description', rows: 2 },
            { key: 'icon', type: 'select', label: 'Icon', options: opts(CATEGORY_ICONS), empty: 'None' },
            { key: 'slug', type: 'text', label: 'URL anchor', help: 'Leave empty to generate from the English name.', dir: 'ltr' },
            { key: 'image', type: 'image', label: 'Image', folder: 'menu' },
            active,
        ],
    },

    'menu-items': {
        title: 'Menu items',
        singular: 'item',
        sortable: true,
        filters: [{ key: 'menu_category_id', type: 'remote', source: 'menu-categories', label: 'All categories' }],
        columns: [
            { key: 'image_url', type: 'thumb' },
            { key: 'name', type: 'trans', label: 'Name' },
            { key: 'category.name', type: 'trans', label: 'Category' },
            { key: 'price', type: 'price', label: 'Price' },
            { key: 'is_featured', type: 'toggle', label: 'Featured' },
            { key: 'is_available', type: 'toggle', label: 'In stock' },
        ],
        fields: [
            { key: 'menu_category_id', type: 'remote', source: 'menu-categories', label: 'Category', required: true },
            { key: 'name', type: 'trans', label: 'Name', required: true },
            { key: 'description', type: 'transText', label: 'Description', rows: 2 },
            { key: 'price', type: 'number', label: 'Price (AED)', step: '0.5', half: true },
            { key: 'price_old', type: 'number', label: 'Old price (shown struck through)', step: '0.5', half: true },
            { key: 'price_note', type: 'text', label: 'Price note', help: 'e.g. "per head", "from", "2 pcs"' },
            { key: 'tags', type: 'tags', label: 'Tags', options: opts(MENU_TAGS) },
            { key: 'image', type: 'image', label: 'Photo', folder: 'menu' },
            { key: 'is_featured', type: 'toggle', label: 'Feature on the homepage' },
            { key: 'is_available', type: 'toggle', label: 'Available today', default: true },
            active,
        ],
    },

    gallery: {
        title: 'Gallery',
        singular: 'photo',
        sortable: true,
        grid: true,
        bulk: true,
        filters: [{ key: 'category', type: 'select', options: opts(GALLERY_CATEGORIES), label: 'All categories' }],
        columns: [{ key: 'image_url', type: 'thumb' }, { key: 'category', label: 'Category' }],
        fields: [
            { key: 'image', type: 'image', label: 'Photo', required: true, folder: 'gallery' },
            { key: 'category', type: 'select', label: 'Category', options: opts(GALLERY_CATEGORIES), required: true, default: 'interior' },
            { key: 'caption', type: 'trans', label: 'Caption' },
            { key: 'alt_en', type: 'text', label: 'Alt text (English)', help: 'Describes the photo for screen readers and Google.', half: true },
            { key: 'alt_ar', type: 'text', label: 'Alt text (Arabic)', dir: 'rtl', half: true },
            { key: 'is_featured', type: 'toggle', label: 'Show on the homepage' },
            active,
        ],
    },

    events: {
        title: 'Match nights & events',
        singular: 'fixture',
        paginated: true,
        filters: [
            { key: 'when', type: 'select', options: [{ value: 'upcoming', label: 'Upcoming' }, { value: 'past', label: 'Past' }], label: 'All dates', default: 'upcoming' },
            { key: 'kind', type: 'select', options: [{ value: 'match', label: 'Matches' }, { value: 'event', label: 'Events' }], label: 'All types' },
        ],
        columns: [
            { key: 'starts_at', type: 'datetime', label: 'Kick-off' },
            { key: 'title', type: 'trans', label: 'Title' },
            { key: 'seats_taken', type: 'seats', label: 'Booked' },
            { key: 'is_big_match', type: 'toggle', label: 'Big match' },
            { key: 'reservations_open', type: 'toggle', label: 'Bookings' },
        ],
        fields: [
            { key: 'kind', type: 'select', label: 'Type', options: [{ value: 'match', label: 'Football match' }, { value: 'event', label: 'Themed night / event' }], required: true, default: 'match', half: true },
            { key: 'starts_at', type: 'datetime', label: 'Starts (Abu Dhabi time)', required: true, half: true },
            { key: 'title', type: 'trans', label: 'Title', required: true, help: 'e.g. "Semi-final" or "Jazz Night".' },
            { key: 'competition', type: 'text', label: 'Competition', help: 'e.g. UEFA Champions League, Saudi Pro League', show: (f) => f.kind === 'match' },
            { key: 'stage', type: 'trans', label: 'Stage / round', show: (f) => f.kind === 'match' },
            { key: 'home_team', type: 'trans', label: 'Home team', show: (f) => f.kind === 'match' },
            { key: 'home_flag', type: 'text', label: 'Home flag code', help: 'Country code: ae, sa, eg, br, ar, eng…', dir: 'ltr', half: true, show: (f) => f.kind === 'match' },
            { key: 'away_flag', type: 'text', label: 'Away flag code', dir: 'ltr', half: true, show: (f) => f.kind === 'match' },
            { key: 'away_team', type: 'trans', label: 'Away team', show: (f) => f.kind === 'match' },
            { key: 'description', type: 'transText', label: 'Description', rows: 3 },
            { key: 'offer', type: 'trans', label: 'Match-night offer', help: 'e.g. "Shisha + 2 drinks AED 99 during the match"' },
            { key: 'image', type: 'image', label: 'Image', folder: 'events' },
            { key: 'capacity', type: 'number', label: 'Seats available for booking', help: 'Leave empty for no limit.' },
            { key: 'is_big_match', type: 'toggle', label: 'Big match (highlighted on the site)' },
            { key: 'reservations_open', type: 'toggle', label: 'Accept reservations', default: true },
            active,
        ],
    },

    offers: {
        title: 'Offers & campaigns',
        singular: 'offer',
        sortable: true,
        columns: [
            { key: 'title', type: 'trans', label: 'Title' },
            { key: 'starts_on', label: 'From' },
            { key: 'ends_on', label: 'Until' },
        ],
        fields: [
            { key: 'title', type: 'trans', label: 'Title', required: true },
            { key: 'badge', type: 'trans', label: 'Badge', help: 'Short label, e.g. "5–6 PM daily"' },
            { key: 'description', type: 'transText', label: 'Description', rows: 3 },
            { key: 'cta_link', type: 'url', label: 'Button link', help: 'Internal path like /reserve or a full URL.' },
            { key: 'starts_on', type: 'date', label: 'Starts', half: true },
            { key: 'ends_on', type: 'date', label: 'Ends', half: true, help: 'Hidden automatically after this date.' },
            { key: 'image', type: 'image', label: 'Image', folder: 'offers' },
            active,
        ],
    },

    testimonials: {
        title: 'Reviews',
        singular: 'review',
        sortable: true,
        columns: [
            { key: 'author', label: 'Guest' },
            { key: 'rating', type: 'stars', label: 'Rating' },
            { key: 'source', label: 'Source' },
        ],
        fields: [
            { key: 'author', type: 'text', label: 'Guest name', required: true, half: true },
            { key: 'source', type: 'select', label: 'Source', required: true, default: 'google', half: true, options: opts(['google', 'instagram', 'tripadvisor', 'in-store']) },
            { key: 'content', type: 'transText', label: 'Review', required: true, rows: 4, help: 'Only publish genuine reviews, quoted as written.' },
            { key: 'rating', type: 'select', label: 'Stars', required: true, default: 5, half: true, options: [5, 4, 3, 2, 1].map((n) => ({ value: n, label: '★'.repeat(n) })) },
            { key: 'reviewed_on', type: 'date', label: 'Review date', half: true },
            active,
        ],
    },

    sections: {
        title: 'Homepage sections',
        singular: 'section',
        sortable: true,
        fixedFilters: { page: 'home' },
        columns: [
            { key: 'type', label: 'Block' },
            { key: 'title', type: 'trans', label: 'Heading' },
        ],
        fields: [
            { key: 'page', type: 'text', label: 'Page', default: 'home', hidden: true },
            { key: 'type', type: 'select', label: 'Block type', options: opts(SECTION_TYPES), required: true, default: 'story' },
            { key: 'title', type: 'trans', label: 'Heading' },
            { key: 'subtitle', type: 'transText', label: 'Subheading', rows: 2, show: is('hero', 'matches', 'visit', 'featured_menu', 'gallery', 'offers', 'testimonials') },
            { key: 'body', type: 'transText', label: 'Text', rows: 6, show: is('story') },
            { key: 'cta_label', type: 'trans', label: 'Button label', show: is('hero', 'story', 'featured_menu', 'matches', 'gallery') },
            { key: 'cta_link', type: 'url', label: 'Button link', show: is('hero', 'story', 'featured_menu', 'matches', 'gallery') },
            { key: 'data.secondary_label', type: 'trans', label: 'Second button label', show: is('hero') },
            { key: 'data.secondary_link', type: 'url', label: 'Second button link', show: is('hero') },
            { key: 'image', type: 'image', label: 'Image (daytime hero)', folder: 'sections', show: is('hero') },
            { key: 'image_secondary', type: 'image', label: 'Image (night hero)', folder: 'sections', show: is('hero') },
            { key: 'image', type: 'image', label: 'Image', folder: 'sections', show: is('story') },
            { key: 'data.day.title', type: 'trans', label: 'Day: heading', show: is('day_night') },
            { key: 'data.day.time', type: 'trans', label: 'Day: time label', show: is('day_night') },
            { key: 'data.day.body', type: 'transText', label: 'Day: text', rows: 3, show: is('day_night') },
            { key: 'data.day.image', type: 'image', label: 'Day: image', folder: 'sections', show: is('day_night') },
            { key: 'data.night.title', type: 'trans', label: 'Night: heading', show: is('day_night') },
            { key: 'data.night.time', type: 'trans', label: 'Night: time label', show: is('day_night') },
            { key: 'data.night.body', type: 'transText', label: 'Night: text', rows: 3, show: is('day_night') },
            { key: 'data.night.image', type: 'image', label: 'Night: image', folder: 'sections', show: is('day_night') },
            active,
        ],
    },
};

/* ---------- Path helpers (fields may target nested JSON like data.day.title) ---------- */
export function getPath(obj, path) {
    return path.split('.').reduce((o, k) => (o == null ? undefined : o[k]), obj);
}
export function setPath(obj, path, value) {
    const keys = path.split('.');
    let o = obj;
    keys.slice(0, -1).forEach((k) => {
        if (o[k] == null || typeof o[k] !== 'object') o[k] = {};
        o = o[k];
    });
    o[keys.at(-1)] = value;
}
