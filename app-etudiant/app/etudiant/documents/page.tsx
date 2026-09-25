import { getMesDocuments } from "@/lib/etudiant/data";
import { TYPE_DOCUMENT_LABELS, formatDateFr } from "@/lib/academique/constants";

export default async function DocumentsPage() {
  const documents = await getMesDocuments();

  return (
    <div className="flex flex-col gap-6">
      <div>
        <h1 className="font-heading text-2xl text-ink">Documents</h1>
        <p className="text-sm text-ink-soft">Vos documents administratifs.</p>
      </div>

      {documents.length === 0 ? (
        <p className="rounded-lg border border-border bg-surface-alt px-4 py-8 text-center text-sm text-ink-soft">
          Aucun document disponible pour le moment.
        </p>
      ) : (
        <ul className="flex flex-col divide-y divide-border rounded-lg border border-border">
          {documents.map((d) => (
            <li key={d.id} className="flex items-center justify-between px-4 py-3 text-sm">
              <div>
                {d.url ? (
                  <a
                    href={d.url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="font-medium text-primary hover:underline"
                  >
                    {TYPE_DOCUMENT_LABELS[d.type]}
                  </a>
                ) : (
                  <span className="font-medium text-ink">{TYPE_DOCUMENT_LABELS[d.type]}</span>
                )}
              </div>
              <span className="text-ink-soft">{formatDateFr(d.genere_le)}</span>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
