<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Récapitulatif du planning</title>
    <style>
        @page {
            margin: 28px;
        }

        body {
            color: #1d2b3a;
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        h1 {
            color: #1769aa;
            font-size: 20px;
            margin: 0 0 6px;
        }

        .planning-meta {
            color: #536273;
            margin-bottom: 22px;
        }

        .expedition + .expedition {
            page-break-before: always;
        }

        .expedition-heading {
            border-bottom: 2px solid #1769aa;
            margin-bottom: 12px;
            padding-bottom: 8px;
        }

        h2 {
            font-size: 16px;
            margin: 0 0 6px;
        }

        .expedition-meta {
            color: #536273;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        thead {
            display: table-header-group;
        }

        th,
        td {
            border: 1px solid #cbd5df;
            padding: 7px;
            text-align: left;
        }

        th {
            background: #eaf2f8;
            color: #183b58;
            font-weight: bold;
        }

        tr {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <h1>Récapitulatif des expéditions</h1>
    <div class="planning-meta">
        Date : {{ $planningDate }}
        <span> | </span>
        Prise de service : {{ substr($planning->hps, 0, 5) }}
    </div>

    @foreach ($expeditions as $group)
        @php($expedition = $group['expedition'])
        @php($passagePoints = $expedition->trajet?->trajet_items?->pluck('point')->implode(' - ') ?? '')
        <section class="expedition">
            <div class="expedition-heading">
                <h2>{{ $expedition->trajet?->designation ?? 'Trajet non renseigné' }}</h2>
                @if ($passagePoints !== '')
                    <div class="expedition-meta">Trajet : {{ $passagePoints }}</div>
                @endif
                <div class="expedition-meta">
                    Expédition #{{ $expedition->id }}
                    <span> | </span>
                    Début du ramassage : {{ substr($expedition->hdr, 0, 5) }}
                    <span> | </span>
                    Véhicule : {{ $expedition->car?->immatriculation ?? 'Non renseigné' }}
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Matricule</th>
                        <th>Nom de l’employé</th>
                        <th>Téléphone</th>
                        <th>Lieu de ramassage</th>
                        <th>Point ADM</th>
                        <th>Point décalé</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($group['items'] as $item)
                        <tr>
                            <td>{{ $item->employe?->matricule ?? '—' }}</td>
                            <td>{{ $item->employe?->nom ?? 'Employé supprimé' }}</td>
                            <td>{{ $item->employe?->telephone ?? '—' }}</td>
                            <td>{{ $item->employe?->lieu_ramassage ?? '—' }}</td>
                            <td>{{ $item->employe?->pnt_adm ?? '—' }}</td>
                            <td>{{ $item->employe?->pnt_dec ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @endforeach
</body>
</html>
