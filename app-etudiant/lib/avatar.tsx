/**
 * Avatar à initiales — pas de vraie photo (aucune n'est demandée aux
 * étudiants à ce jour), jamais une image générique/inventée à la
 * place : un cercle coloré avec les initiales, pattern standard
 * (Gmail, Slack...). Couleur dérivée d'un hash simple du nom, stable
 * pour un même étudiant d'une session à l'autre.
 */

const COULEURS = [
  "bg-primary",
  "bg-teal",
  "bg-accent",
  "bg-red",
  "bg-primary-dark",
] as const;

function hashString(value: string): number {
  let hash = 0;
  for (let i = 0; i < value.length; i++) {
    hash = (hash << 5) - hash + value.charCodeAt(i);
    hash |= 0;
  }
  return Math.abs(hash);
}

function getInitiales(prenom: string, nom: string): string {
  const p = prenom.trim().charAt(0).toUpperCase();
  const n = nom.trim().charAt(0).toUpperCase();
  return `${p}${n}` || "?";
}

const TAILLES = {
  sm: "h-8 w-8 text-xs",
  md: "h-11 w-11 text-sm",
  lg: "h-16 w-16 text-xl",
} as const;

export function Avatar({
  prenom,
  nom,
  taille = "md",
  className = "",
}: {
  prenom: string;
  nom: string;
  taille?: keyof typeof TAILLES;
  className?: string;
}) {
  const initiales = getInitiales(prenom, nom);
  const couleur = COULEURS[hashString(`${prenom}${nom}`) % COULEURS.length];
  const accentSurAccent = couleur === "bg-accent"; // accent = orange clair, texte noir plus lisible que blanc
  const isTextOnAccent = accentSurAccent ? "text-ink" : "text-white";

  return (
    <span
      aria-hidden="true"
      className={`inline-flex shrink-0 items-center justify-center rounded-full font-heading font-semibold ${couleur} ${isTextOnAccent} ${TAILLES[taille]} ${className}`}
    >
      {initiales}
    </span>
  );
}
