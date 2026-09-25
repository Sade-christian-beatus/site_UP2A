/**
 * Icônes SVG en ligne, dessinées à la main — pas de police d'icônes ni
 * de librairie externe (cohérent avec CLAUDE.md §7 "self-host, pas de
 * multi-CDN" et le même parti pris déjà appliqué côté WordPress dans
 * up2a-core, voir up2a_core_content_icon()). JSX plutôt que
 * dangerouslySetInnerHTML : pas de HTML injecté, même statique.
 */
import type { SVGProps, ReactElement } from "react";

export type IconName =
  | "dashboard"
  | "calendar"
  | "book"
  | "clipboard"
  | "chart"
  | "file"
  | "megaphone"
  | "logout"
  | "menu"
  | "close";

function Svg({ children, className, ...props }: SVGProps<SVGSVGElement>) {
  return (
    <svg
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="1.8"
      strokeLinecap="round"
      strokeLinejoin="round"
      aria-hidden="true"
      className={className ?? "h-5 w-5"}
      {...props}
    >
      {children}
    </svg>
  );
}

const ICONS: Record<IconName, (props: SVGProps<SVGSVGElement>) => ReactElement> = {
  dashboard: (props) => (
    <Svg {...props}>
      <rect x="3" y="3" width="7" height="9" rx="1.5" />
      <rect x="14" y="3" width="7" height="5" rx="1.5" />
      <rect x="14" y="12" width="7" height="9" rx="1.5" />
      <rect x="3" y="16" width="7" height="5" rx="1.5" />
    </Svg>
  ),
  calendar: (props) => (
    <Svg {...props}>
      <rect x="3" y="5" width="18" height="16" rx="2" />
      <path d="M16 3v4M8 3v4M3 10h18" />
    </Svg>
  ),
  book: (props) => (
    <Svg {...props}>
      <path d="M4 5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v15l-4-2-4 2Z" />
      <path d="M20 5a2 2 0 0 0-2-2h-4v17l4-2 2 1Z" />
    </Svg>
  ),
  clipboard: (props) => (
    <Svg {...props}>
      <rect x="5" y="4" width="14" height="17" rx="2" />
      <rect x="9" y="2" width="6" height="4" rx="1" />
      <path d="m9 13 2 2 4-4" />
    </Svg>
  ),
  chart: (props) => (
    <Svg {...props}>
      <path d="M4 20V10M11 20V4M18 20v-7" />
      <path d="M3 20h18" />
    </Svg>
  ),
  file: (props) => (
    <Svg {...props}>
      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" />
      <path d="M14 2v6h6" />
      <path d="M9 13h6M9 17h6" />
    </Svg>
  ),
  megaphone: (props) => (
    <Svg {...props}>
      <path d="M3 9v6h4l6 4V5L7 9Z" />
      <path d="M16 9a3 3 0 0 1 0 6" />
      <path d="M19 6a7 7 0 0 1 0 12" />
    </Svg>
  ),
  logout: (props) => (
    <Svg {...props}>
      <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
      <path d="M16 17l5-5-5-5" />
      <path d="M21 12H9" />
    </Svg>
  ),
  menu: (props) => (
    <Svg {...props}>
      <line x1="3" y1="6" x2="21" y2="6" />
      <line x1="3" y1="12" x2="21" y2="12" />
      <line x1="3" y1="18" x2="21" y2="18" />
    </Svg>
  ),
  close: (props) => (
    <Svg {...props}>
      <line x1="18" y1="6" x2="6" y2="18" />
      <line x1="6" y1="6" x2="18" y2="18" />
    </Svg>
  ),
};

export function Icon({
  name,
  className,
}: {
  name: IconName;
  className?: string;
}) {
  const Cmp = ICONS[name];
  return <Cmp className={className} />;
}
