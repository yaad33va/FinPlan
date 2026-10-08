/**
 * Vector (SVG) icons from the Lucide icon set (npm package "lucide", ISC licence).
 * Only the icons imported here end up in the JavaScript bundle.
 *
 * In Blade:      <i data-lucide="pencil"></i>   – replaced by an <svg> in renderIcons()
 * In JavaScript: icon('pencil')                 – returns an <svg> element
 */
import {
    ArrowLeft,
    ArrowRight,
    Banknote,
    BookOpen,
    Calendar,
    ChevronLeft,
    ChevronRight,
    CircleAlert,
    CircleCheck,
    CircleUser,
    CircleX,
    CodeXml,
    CreditCard,
    Eye,
    EyeOff,
    FolderOpen,
    HandCoins,
    House,
    Info,
    Landmark,
    LayoutDashboard,
    Lock,
    LogIn,
    LogOut,
    Mail,
    Pencil,
    PiggyBank,
    Plus,
    QrCode,
    Receipt,
    Save,
    Scale,
    Search,
    ShieldCheck,
    ShieldOff,
    Smartphone,
    Sparkles,
    Target,
    Trash,
    TrendingDown,
    TrendingUp,
    TriangleAlert,
    User,
    UserPlus,
    Users,
    Wallet,
    X,
    createElement,
    createIcons,
} from 'lucide';

const icons = {
    ArrowLeft,
    ArrowRight,
    Banknote,
    BookOpen,
    Calendar,
    ChevronLeft,
    ChevronRight,
    CircleAlert,
    CircleCheck,
    CircleUser,
    CircleX,
    CodeXml,
    CreditCard,
    Eye,
    EyeOff,
    FolderOpen,
    HandCoins,
    House,
    Info,
    Landmark,
    LayoutDashboard,
    Lock,
    LogIn,
    LogOut,
    Mail,
    Pencil,
    PiggyBank,
    Plus,
    QrCode,
    Receipt,
    Save,
    Scale,
    Search,
    ShieldCheck,
    ShieldOff,
    Smartphone,
    Sparkles,
    Target,
    Trash,
    TrendingDown,
    TrendingUp,
    TriangleAlert,
    User,
    UserPlus,
    Users,
    Wallet,
    X,
};

const toPascalCase = (name) => name.replace(/(^|-)([a-z0-9])/g, (_, __, char) => char.toUpperCase());

/** Replaces every <i data-lucide="..."> inside root with an inline SVG icon. */
export function renderIcons(root = document) {
    createIcons({ icons, root, attrs: { class: 'icon' } });
}

/** Creates an SVG icon element, e.g. icon('trash'). */
export function icon(name, extraClass = '') {
    const node = icons[toPascalCase(name)];

    if (!node) {
        throw new Error(`Unknown icon "${name}". Import it in resources/js/lib/icons.js.`);
    }

    return createElement(node, { class: `icon ${extraClass}`.trim(), 'aria-hidden': 'true' });
}
