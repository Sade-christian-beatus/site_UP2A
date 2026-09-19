import { createServerClient } from "@supabase/ssr";
import { NextResponse, type NextRequest } from "next/server";

const ROUTES_PROTEGEES = ["/etudiant", "/admin"];
const ROUTES_AUTH = ["/connexion"];

/**
 * Rafraîchit la session Supabase à chaque requête et applique une garde
 * d'authentification "optimiste" (présence d'une session uniquement).
 *
 * Volontairement PAS de vérification de rôle ici : le Proxy Next.js
 * s'exécute sur (quasiment) toutes les requêtes, y compris les
 * prefetchs, et ne doit donc faire que des vérifications bon marché
 * (cookie de session). La vérification de rôle "sûre" (étudiant/admin)
 * se fait plus près de la donnée, dans lib/auth/dal.ts, appelée depuis
 * les layouts de app/etudiant et app/admin.
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
  const estRouteProtegee = ROUTES_PROTEGEES.some((route) =>
    pathname.startsWith(route),
  );
  const estRouteAuth = ROUTES_AUTH.some((route) => pathname.startsWith(route));

  if (!user && estRouteProtegee) {
    const url = request.nextUrl.clone();
    url.pathname = "/connexion";
    return NextResponse.redirect(url);
  }

  if (user && estRouteAuth) {
    const url = request.nextUrl.clone();
    url.pathname = "/";
    return NextResponse.redirect(url);
  }

  return response;
}
