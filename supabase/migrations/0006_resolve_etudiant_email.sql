-- 0006_resolve_etudiant_email.sql
-- Résolution matricule → e-mail, pour permettre à un étudiant de se
-- connecter avec son matricule plutôt qu'avec son adresse e-mail (voir
-- docs/06-storyboard.md "Login étudiant par matricule"). Le SMTP du
-- projet n'étant pas configuré (et le client ayant explicitement
-- renoncé à le configurer), l'e-mail de l'étudiant n'est utile qu'en
-- interne (identifiant Supabase Auth) : le matricule devient le seul
-- identifiant que l'étudiant a réellement besoin de connaître.
--
-- SECURITY DEFINER nécessaire ici pour deux raisons : lire
-- `auth.users.email` (normalement inaccessible même en lecture au rôle
-- `anon`) et fonctionner AVANT toute authentification (l'étudiant n'a
-- encore aucune session au moment de cet appel, donc aucune policy RLS
-- "self" ne pourrait s'appliquer).
--
-- Limite connue : cette fonction est un oracle "ce matricule existe-t-il
-- et à quel e-mail correspond-il" pour quiconque dispose de la clé
-- publique `anon` (donc n'importe quel visiteur du site) — elle ne
-- protège pas contre l'énumération de matricules valides. Acceptable
-- pour la taille de cette université (quelques centaines d'étudiants,
-- matricules non secrets en eux-mêmes), mais à garder en tête si le
-- projet grandit : un vrai rate-limiting (ex. Edge Function dédiée avec
-- limite par IP) serait la suite logique plutôt qu'une fonction RPC
-- ouverte sans limite.

create or replace function public.resolve_etudiant_email(p_matricule text)
returns text
language sql
stable
security definer
set search_path = public, auth
as $$
  select au.email
  from public.etudiants et
  join public.profiles p on p.id = et.profile_id
  join auth.users au on au.id = p.id
  where et.matricule = p_matricule
    and et.statut = 'actif'
  limit 1;
$$;

comment on function public.resolve_etudiant_email(text) is
  'Résout le matricule d''un étudiant actif vers son e-mail Supabase Auth, '
  'pour permettre une connexion par matricule (voir app-etudiant/lib/auth/actions.ts). '
  'Retourne null si le matricule est inconnu ou l''étudiant inactif.';

revoke all on function public.resolve_etudiant_email(text) from public;
grant execute on function public.resolve_etudiant_email(text) to anon, authenticated;
