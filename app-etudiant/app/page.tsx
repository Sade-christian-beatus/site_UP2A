import { redirect } from "next/navigation";
import { getProfile } from "@/lib/auth/dal";

export default async function HomePage() {
  const profile = await getProfile();

  if (!profile) {
    redirect("/connexion");
  }

  redirect(profile.role === "admin" ? "/admin" : "/etudiant");
}
