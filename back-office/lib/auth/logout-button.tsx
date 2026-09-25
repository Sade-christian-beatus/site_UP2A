"use client";

import { logout } from "@/lib/auth/actions";

export function LogoutButton() {
  return (
    <form action={logout}>
      <button
        type="submit"
        className="rounded-md border border-border px-3 py-1.5 text-sm text-ink-soft transition-colors hover:border-primary hover:text-primary"
      >
        Se déconnecter
      </button>
    </form>
  );
}
