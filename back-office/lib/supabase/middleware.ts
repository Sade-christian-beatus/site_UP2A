import { createServerClient } from "@supabase/ssr";
import { NextResponse, type NextRequest } from "next/server";

const ROUTES_PUBLIQUES = ["/connexion"];

/**
 * Rafraîchit la session Supabase à chaque requête et applique une garde
 * d'authentification "optimiste" (présence d'une session uniquement).
 *
 * Cette app étant désormais mono-usage (back-office uniquement, depuis
 * la séparation de l'espace étudiant en application distincte), toute
 * route qui n'est pas `/connexion` est protégée — pas besoin d'une
 * liste explicite comme quand app-etudiant portait les deux espaces.
 *
 * Volontairement PAS de vérification de rôle ici : le Proxy s'exécute
 * sur (quasiment) toutes les requêtes, y compris les prefetchs, et ne
 * doit donc faire que des vérifications bon marché (cookie de session).
 * La vérification de rôle "sûre" (admin) se fait plus près de la
 * donnée, dans lib/auth/dal.ts, appelée depuis app/(app)/layout.tsx.
 */
export async function updateSession(request: NextRequest) {
  let response = NextResponse.next({ request });

  const supabase = createServerClient(
    process.env.NEXT_PUBLIC_SUPABASE_URL!,
    process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY!,
    {
      cookies: {
        getAll() {
          return request.cookies.getAll();
        },
        setAll(cookiesToSet) {
          cookiesToSet.forEach(({ name, value }) =>
            request.cookies.set(name, value),
          );
          response = NextResponse.next({ request });
          cookiesToSet.forEach(({ name, value, options }) =>
            response.cookies.set(name, value, options),
          );
        },
      },
    },
  );

  const {
    data: { user },
  } = await supabase.auth.getUser();

  const { pathname } = request.nextUrl;
  const estRoutePublique = ROUTES_PUBLIQUES.some((route) =>
    pathname.startsWith(route),
  );

  if (!user && !estRoutePublique) {
    const url = request.nextUrl.clone();
    url.pathname = "/connexion";
    return NextResponse.redirect(url);
  }

  if (user && estRoutePublique) {
    const url = request.nextUrl.clone();
    url.pathname = "/";
    return NextResponse.redirect(url);
  }

  return response;
}
