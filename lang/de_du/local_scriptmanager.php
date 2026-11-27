<?php
// This file is part of Moodle - https://moodle.org/
/**
 * German language strings for Script Manager.
 *
 * @package local_scriptmanager
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license 108design source-available software license; see LICENSE.md
 */

// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>. See LICENSE.md.

$string['fixedprofile'] = 'Aktive veröffentlichte Skripte laufen auf normalen Moodle-Seiten. Für Frontpage-Ansichten wählst du „Auch auf Frontpage-Seiten ausführen“ und veröffentlichst das Skript. Editoren und Vorschauen bleiben ausgenommen.';
$string['moduleownershipconflict'] = 'Für die Skriptkonfiguration bestehen widersprüchliche Zuständigkeiten. Prüfe die vorhandene Zuordnung; sie wechselt nicht automatisch.';
$string['pagehelp_scriptbasics_article'] = '<p>Lege benannte Snippets mit eindeutigen Kennungen an. Suche nach Name, Kennung oder Beschreibung und filterst nach Aktiv- oder Veröffentlichungsstatus. Neue Snippets beginnen deaktiviert und ohne veröffentlichte Fassung. Ein gespeicherter Entwurf lässt den bisherigen Live-Stand bestehen. Mehrere offene Editoren können sich überschreiben: Der zuletzt gespeicherte Entwurf gewinnt.</p>';
$string['pagehelp_scriptbasics_help'] = 'Lege benannte Snippets mit eindeutigen Kennungen an.';
$string['pagehelp_scriptexecution_article'] = '<p>Aktive veröffentlichte Skripte laufen am Footer-Hook auf normalen Moodle-Seiten. Für die Frontpage-Startseite von Gästen und Mitgliedern und die normale Ansicht von Frontpage-Kursaktivitäten wählst du <strong>Auch auf Frontpage-Seiten ausführen</strong> und veröffentlichst das Skript. Die Option ist standardmäßig aus. Frontpage-Editoren, Entwurfs- und Versionsvorschauen bleiben ausgenommen.</p><p>Skripte bleiben auch bei der Installation, in der Verwaltung, bei der Anmeldung, in der Authentifizierung, in Tools, im Skriptmanager und in den Layouts embedded, popup, maintenance, print und redirect aus. Wenn ein Skript eine Seite stört, kannst du dich weiterhin anmelden und es im Skriptmanager abschalten. Ein Zeitpunkt nach DOMContentLoaded wird nicht garantiert. context enthält root, pageType, pageId, courseId und categoryId. Bei Moodles Inhaltsaktualisierungsereignis werden registrierte Snippets mit dem geänderten root erneut aufgerufen; schreibe wiederholbar initialisierbaren Code. JavaScript kann auf Seite und Netzwerk zugreifen. Synchrone Fehler werden protokolliert; Schleifen, asynchrone Fehler und bereits ausgeführte Änderungen sind nicht isoliert. Die alphabetische Auswahl wartet nicht auf asynchrone Arbeit des vorherigen Snippets.</p>';
$string['pagehelp_scriptidentity_article'] = '<p>Verwende einen verständlichen Namen und eine eindeutige Kennung aus Buchstaben, Ziffern, Unterstrichen oder Bindestrichen. Die Kennung benennt die Registrierung zur Laufzeit. Behalte sie bei bereits genutzten Skripten möglichst bei.</p>';
$string['pagehelp_scriptidentity_help'] = 'Verwende einen verständlichen Namen und eine eindeutige Kennung aus Buchstaben, Ziffern, Unterstrichen oder Bindestrichen.';
$string['pagehelp_scriptrevisions_article'] = '<p>Veröffentlichte Fassungen speichern Code, Notiz, Autor/in und Zeitpunkt. Vergleiche zwei Versionen oder sieh ihren Quelltext an. Wiederherstellen ersetzt den aktuellen Entwurf durch den gewählten Code. Prüfe ihn und veröffentliche ihn erneut bewusst; der Live-Stand wechselt beim Wiederherstellen nicht. Auswirkungen früher ausgeführten Codes werden dadurch nicht rückgängig gemacht.</p>';
$string['scriptmanagernotice'] = 'Skripte sind vertrauenswürdiger Administrationscode. Speichere einen Entwurf und veröffentliche ihn bewusst. Der Aktivstatus steuert künftige Auslieferungen; bereits ausgeführter oder gecachter Code wird dadurch nicht widerrufen.';
$string['scriptrevisionhistorynotice'] = 'Das Laden einer Revision ersetzt den aktuellen Entwurf und öffnet ihn im Skripteditor. Sie wird nicht automatisch veröffentlicht; prüfe sie dort und veröffentliche sie anschließend ausdrücklich als neue Revision.';
$string['scriptrevisionloaded'] = 'Revision {$a} wurde als aktueller Entwurf geladen. Es wurde noch nichts veröffentlicht. Prüfen oder bearbeitest du den Entwurf und verwendest anschließend „Revision als neue Version veröffentlichen“.';
$string['scriptsecuritynotice'] = '<strong>Vertrauenswürdiges JavaScript:</strong> Der Code läuft mit den Browserrechten der jeweiligen Besucher/innen. Prüfe ihn vor der Veröffentlichung und speichere hier keine Geheimnisse. context.root hilft beim gezielten Initialisieren aktualisierter Inhalte; es begrenzt die JavaScript-Rechte nicht.';
$string['unsupportedupgrade'] = 'Dieses Paket kann die installierte Version nicht aktualisieren. Stelle die vorherigen Plugin-Dateien wieder her. Installiere diese Version als neue Kopie von Script Manager.';
$string['updateguard_hint'] = 'Verwende ein freigegebenes Paket für die installierte Ausgabe. Du kannst den Vorgang erneut prüfen.';
$string['updateguard_dependency'] = 'Eine benötigte Plugin-Version fehlt. Aktualisiere die Abhängigkeit gemeinsam mit diesem Plugin oder vorher.';
$string['updateguard_approval'] = 'Die Pakete oder der installierte Stand haben sich seit der Vorschau geändert. Prüfe den Vorgang erneut.';
$string['updateguard_busy'] = 'Eine andere Installation läuft. Versuche es nach deren Abschluss erneut.';
$string['updateguard_installed'] = 'Der installierte Updateschutz ist unvollständig. Stelle die Dateien der zuletzt installierten freigegebenen Version wieder her.';
$string['updateguard_operation'] = 'Diese Kombination von Installationsaktionen wird nicht unterstützt. Starte einen einzelnen Updatevorgang erneut.';
$string['updateinfo_choice'] = 'Kontrolliere vor dem Fortfahren die Paketquelle und Moodles Prüfergebnisse. Mit „{$a}“ unten kannst du abbrechen.';
