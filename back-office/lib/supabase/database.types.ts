// Types générés à la main à partir de supabase/migrations/0001_init.sql,
// 0002_rls.sql et 0003_seed.sql.
//
// ⚠️ Normalement ce fichier doit être généré automatiquement avec :
//   npx supabase gen types typescript --local > lib/supabase/database.types.ts
// (voir docs/04-conventions.md). Cette commande nécessite le stack Supabase
// local (Docker), indisponible dans l'environnement d'exécution qui a créé
// ce scaffold. Le fichier a donc été écrit à la main pour rester exact — à
// régénérer via la commande ci-dessus dès que Docker/Supabase CLI sont
// disponibles, et à chaque nouvelle migration.

export type Json =
  | string
  | number
  | boolean
  | null
  | { [key: string]: Json | undefined }
  | Json[];

export interface Database {
  public: {
    Tables: {
      profiles: {
        Row: {
          id: string;
          role: Database["public"]["Enums"]["role_utilisateur"];
          nom: string;
          prenom: string;
          telephone: string | null;
          created_at: string;
        };
        Insert: {
          id: string;
          role?: Database["public"]["Enums"]["role_utilisateur"];
          nom: string;
          prenom: string;
          telephone?: string | null;
          created_at?: string;
        };
        Update: {
          id?: string;
          role?: Database["public"]["Enums"]["role_utilisateur"];
          nom?: string;
          prenom?: string;
          telephone?: string | null;
          created_at?: string;
        };
        Relationships: [];
      };
      facultes: {
        Row: {
          id: string;
          nom: string;
          slug: string;
          description: string | null;
          created_at: string;
        };
        Insert: {
          id?: string;
          nom: string;
          slug: string;
          description?: string | null;
          created_at?: string;
        };
        Update: {
          id?: string;
          nom?: string;
          slug?: string;
          description?: string | null;
          created_at?: string;
        };
        Relationships: [];
      };
      formations: {
        Row: {
          id: string;
          faculte_id: string;
          nom: string;
          slug: string;
          niveau: string;
          duree_annees: number;
          description: string | null;
          created_at: string;
        };
        Insert: {
          id?: string;
          faculte_id: string;
          nom: string;
          slug: string;
          niveau?: string;
          duree_annees?: number;
          description?: string | null;
          created_at?: string;
        };
        Update: {
          id?: string;
          faculte_id?: string;
          nom?: string;
          slug?: string;
          niveau?: string;
          duree_annees?: number;
          description?: string | null;
          created_at?: string;
        };
        Relationships: [];
      };
      annees_academiques: {
        Row: {
          id: string;
          libelle: string;
          date_debut: string;
          date_fin: string;
          est_courante: boolean;
          created_at: string;
        };
        Insert: {
          id?: string;
          libelle: string;
          date_debut: string;
          date_fin: string;
          est_courante?: boolean;
          created_at?: string;
        };
        Update: {
          id?: string;
          libelle?: string;
          date_debut?: string;
          date_fin?: string;
          est_courante?: boolean;
          created_at?: string;
        };
        Relationships: [];
      };
      candidatures: {
        Row: {
          id: string;
          created_at: string;
          updated_at: string;
          nom: string;
          prenom: string;
          date_naissance: string;
          sexe: string | null;
          email: string;
          telephone: string;
          adresse: string | null;
          formation_id: string;
          annee_academique_id: string;
          diplome_obtenu: string | null;
          etablissement_origine: string | null;
          pieces_jointes: Json;
          statut: Database["public"]["Enums"]["statut_candidature"];
          notes_admin: string | null;
        };
        Insert: {
          id?: string;
          created_at?: string;
          updated_at?: string;
          nom: string;
          prenom: string;
          date_naissance: string;
          sexe?: string | null;
          email: string;
          telephone: string;
          adresse?: string | null;
          formation_id: string;
          annee_academique_id: string;
          diplome_obtenu?: string | null;
          etablissement_origine?: string | null;
          pieces_jointes?: Json;
          statut?: Database["public"]["Enums"]["statut_candidature"];
          notes_admin?: string | null;
        };
        Update: {
          id?: string;
          created_at?: string;
          updated_at?: string;
          nom?: string;
          prenom?: string;
          date_naissance?: string;
          sexe?: string | null;
          email?: string;
          telephone?: string;
          adresse?: string | null;
          formation_id?: string;
          annee_academique_id?: string;
          diplome_obtenu?: string | null;
          etablissement_origine?: string | null;
          pieces_jointes?: Json;
          statut?: Database["public"]["Enums"]["statut_candidature"];
          notes_admin?: string | null;
        };
        Relationships: [];
      };
      etudiants: {
        Row: {
          id: string;
          profile_id: string | null;
          candidature_id: string | null;
          matricule: string;
          formation_id: string;
          annee_academique_id: string;
          statut: Database["public"]["Enums"]["statut_etudiant"];
          created_at: string;
        };
        Insert: {
          id?: string;
          profile_id?: string | null;
          candidature_id?: string | null;
          matricule: string;
          formation_id: string;
          annee_academique_id: string;
          statut?: Database["public"]["Enums"]["statut_etudiant"];
          created_at?: string;
        };
        Update: {
          id?: string;
          profile_id?: string | null;
          candidature_id?: string | null;
          matricule?: string;
          formation_id?: string;
          annee_academique_id?: string;
          statut?: Database["public"]["Enums"]["statut_etudiant"];
          created_at?: string;
        };
        Relationships: [];
      };
      emplois_du_temps: {
        Row: {
          id: string;
          formation_id: string;
          annee_academique_id: string;
          jour_semaine: Database["public"]["Enums"]["jour_semaine"];
          heure_debut: string;
          heure_fin: string;
          matiere: string;
          enseignant: string | null;
          salle: string | null;
          created_at: string;
        };
        Insert: {
          id?: string;
          formation_id: string;
          annee_academique_id: string;
          jour_semaine: Database["public"]["Enums"]["jour_semaine"];
          heure_debut: string;
          heure_fin: string;
          matiere: string;
          enseignant?: string | null;
          salle?: string | null;
          created_at?: string;
        };
        Update: {
          id?: string;
          formation_id?: string;
          annee_academique_id?: string;
          jour_semaine?: Database["public"]["Enums"]["jour_semaine"];
          heure_debut?: string;
          heure_fin?: string;
          matiere?: string;
          enseignant?: string | null;
          salle?: string | null;
          created_at?: string;
        };
        Relationships: [];
      };
      supports_cours: {
        Row: {
          id: string;
          formation_id: string;
          annee_academique_id: string;
          matiere: string;
          titre: string;
          description: string | null;
          fichier_url: string;
          created_by: string | null;
          published_at: string;
        };
        Insert: {
          id?: string;
          formation_id: string;
          annee_academique_id: string;
          matiere: string;
          titre: string;
          description?: string | null;
          fichier_url: string;
          created_by?: string | null;
          published_at?: string;
        };
        Update: {
          id?: string;
          formation_id?: string;
          annee_academique_id?: string;
          matiere?: string;
          titre?: string;
          description?: string | null;
          fichier_url?: string;
          created_by?: string | null;
          published_at?: string;
        };
        Relationships: [];
      };
      examens: {
        Row: {
          id: string;
          formation_id: string;
          annee_academique_id: string;
          matiere: string;
          type: Database["public"]["Enums"]["type_examen"];
          date_examen: string;
          heure_debut: string;
          heure_fin: string;
          salle: string | null;
          created_at: string;
        };
        Insert: {
          id?: string;
          formation_id: string;
          annee_academique_id: string;
          matiere: string;
          type?: Database["public"]["Enums"]["type_examen"];
          date_examen: string;
          heure_debut: string;
          heure_fin: string;
          salle?: string | null;
          created_at?: string;
        };
        Update: {
          id?: string;
          formation_id?: string;
          annee_academique_id?: string;
          matiere?: string;
          type?: Database["public"]["Enums"]["type_examen"];
          date_examen?: string;
          heure_debut?: string;
          heure_fin?: string;
          salle?: string | null;
          created_at?: string;
        };
        Relationships: [];
      };
      resultats: {
        Row: {
          id: string;
          etudiant_id: string;
          examen_id: string;
          note: number;
          mention: string | null;
          publie: boolean;
          created_at: string;
        };
        Insert: {
          id?: string;
          etudiant_id: string;
          examen_id: string;
          note: number;
          mention?: string | null;
          publie?: boolean;
          created_at?: string;
        };
        Update: {
          id?: string;
          etudiant_id?: string;
          examen_id?: string;
          note?: number;
          mention?: string | null;
          publie?: boolean;
          created_at?: string;
        };
        Relationships: [];
      };
      documents: {
        Row: {
          id: string;
          etudiant_id: string;
          type: Database["public"]["Enums"]["type_document"];
          fichier_url: string;
          genere_le: string;
          created_by: string | null;
        };
        Insert: {
          id?: string;
          etudiant_id: string;
          type?: Database["public"]["Enums"]["type_document"];
          fichier_url: string;
          genere_le?: string;
          created_by?: string | null;
        };
        Update: {
          id?: string;
          etudiant_id?: string;
          type?: Database["public"]["Enums"]["type_document"];
          fichier_url?: string;
          genere_le?: string;
          created_by?: string | null;
        };
        Relationships: [];
      };
      annonces: {
        Row: {
          id: string;
          titre: string;
          contenu: string;
          formation_id: string | null;
          annee_academique_id: string | null;
          publie_le: string;
          created_by: string | null;
          created_at: string;
        };
        Insert: {
          id?: string;
          titre: string;
          contenu: string;
          formation_id?: string | null;
          annee_academique_id?: string | null;
          publie_le?: string;
          created_by?: string | null;
          created_at?: string;
        };
        Update: {
          id?: string;
          titre?: string;
          contenu?: string;
          formation_id?: string | null;
          annee_academique_id?: string | null;
          publie_le?: string;
          created_by?: string | null;
          created_at?: string;
        };
        Relationships: [];
      };
    };
    Views: Record<never, never>;
    Functions: {
      is_admin: {
        Args: Record<string, never>;
        Returns: boolean;
      };
      mon_etudiant_id: {
        Args: Record<string, never>;
        Returns: string | null;
      };
      ma_formation_id: {
        Args: Record<string, never>;
        Returns: string | null;
      };
      mon_annee_academique_id: {
        Args: Record<string, never>;
        Returns: string | null;
      };
    };
    Enums: {
      role_utilisateur: "etudiant" | "admin";
      statut_candidature:
        | "nouvelle"
        | "en_cours"
        | "acceptee"
        | "refusee"
        | "transformee";
      statut_etudiant: "actif" | "suspendu" | "diplome" | "abandon";
      type_examen: "partiel" | "final" | "rattrapage";
      type_document:
        | "releve_notes"
        | "certificat_scolarite"
        | "attestation"
        | "autre";
      jour_semaine:
        | "lundi"
        | "mardi"
        | "mercredi"
        | "jeudi"
        | "vendredi"
        | "samedi";
    };
  };
}
