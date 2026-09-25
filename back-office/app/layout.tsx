import type { Metadata } from "next";
import "./globals.css";

export const metadata: Metadata = {
  title: "Back-office — UP-2A",
  description: "Université Privée An-Nahdah d'Afrique — administration",
};

export default function RootLayout({ children }: LayoutProps<"/">) {
  return (
    <html lang="fr" className="h-full antialiased">
      <body className="min-h-full flex flex-col">{children}</body>
    </html>
  );
}
