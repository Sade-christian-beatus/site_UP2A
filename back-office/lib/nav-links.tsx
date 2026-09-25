"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";

/**
 * Barre de navigation avec mise en évidence du lien actif, utilisée par
 * app/(app)/layout.tsx. `usePathname()` exige un Client Component ; le
 * layout lui-même reste un Server Component (requireRole y reste
 * server-only).
 */
export function NavLinks({
  links,
}: {
  links: Array<{ href: string; label: string }>;
}) {
  const pathname = usePathname();

  return (
    <>
      {links.map((link) => {
        const isActive = link.href === "/" ? pathname === "/" : pathname.startsWith(link.href);

        return (
          <Link
            key={link.href}
            href={link.href}
            className={`rounded-md px-3 py-1.5 text-sm font-medium transition-colors ${
              isActive ? "bg-primary text-white" : "text-ink-soft hover:bg-border hover:text-ink"
            }`}
          >
            {link.label}
          </Link>
        );
      })}
    </>
  );
}
