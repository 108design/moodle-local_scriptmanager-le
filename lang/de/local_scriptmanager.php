<?php
/**
 * German language strings for Script Manager.
 *
 * @package local_scriptmanager
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license 108design source-available software license; see LICENSE.md
 */

// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>. See LICENSE.md.
$string['actionbarmore'] = 'Weitere Aktionen';
$string['activatescript'] = 'Skript aktivieren';
$string['contexthelptopics'] = 'Hilfethemen';
$string['createscript'] = 'Skript erstellen';
$string['deactivatescript'] = 'Skript deaktivieren';
$string['deletescript'] = 'Skript löschen';
$string['deletescriptconfirm'] = ('„{$a}“ und sämtliche veröffentlichten Fassungen endgültig löschen? '
    . 'Bereits ausgeführter Code wird dadurch nicht rückgängig gemacht.');
$string['editscript'] = 'Skript bearbeiten';
$string['fixedprofile'] = ('Aktive veröffentlichte Skripte laufen am Footer-Hook auf zugelassenen '
    . 'normalen Moodle-Seiten. Verwaltung, Anmeldung, Authentifizierung, Tools, '
    . 'dieser Manager, Frontpage-Seiten und Sonderlayouts sind ausgeschlossen.');
$string['loadrevisionineditor'] = 'Revision im Editor laden';
$string['managerdateformat'] = '%d. %b %Y, %H:%M';
$string['managescripts'] = 'Skriptmanager';
$string['moduleinactive'] = 'Dieser Manager ist inaktiv. {$a} verwaltet die vorhandene Skriptkonfiguration.';
$string['moduleopenowner'] = '{$a} öffnen';
$string['moduleownershipconflict'] = ('Für die Skriptkonfiguration bestehen widersprüchliche Zuständigkeiten. '
    . 'Prüfe die vorhandene Zuordnung; sie wechselt nicht automatisch.');
$string['noscripts'] = 'Es wurden noch keine verwalteten Skripte erstellt.';
$string['pagehelp_moduleownership'] = 'Vorhandene Zuständigkeit';
$string['pagehelp_moduleownership_article'] = (
        '<p>Vorhandene Zuordnungen bleiben maßgeblich. Wenn ein anderer Host diese '
    . 'Konfiguration verwaltet, übernimmt dieser Manager sie nicht automatisch.</p>'
    );
$string['pagehelp_moduleownership_help'] = 'Vorhandene Zuordnungen bleiben maßgeblich.';
$string['pagehelp_more'] = 'Mehr im Hilfebereich';
$string['pagehelp_scriptbasics'] = 'Skriptliste und Editor';
$string['pagehelp_scriptbasics_article'] = ('<p>Lege benannte Snippets mit eindeutigen Kennungen an. Suche nach Name, '
    . 'Kennung oder Beschreibung und filtere nach Aktiv- oder '
    . 'Veröffentlichungsstatus. Neue Snippets beginnen deaktiviert und ohne '
    . 'veröffentlichte Fassung. Ein gespeicherter Entwurf lässt den bisherigen '
    . 'Live-Stand bestehen. Mehrere offene Editoren können sich überschreiben: Der '
    . 'zuletzt gespeicherte Entwurf gewinnt.</p>');
$string['pagehelp_scriptbasics_help'] = 'Lege benannte Snippets mit eindeutigen Kennungen an.';
$string['pagehelp_scriptexecution'] = 'Ausführung und aktualisierte Inhalte';
$string['pagehelp_scriptexecution_article'] = ('<p>Aktive veröffentlichte Snippets laufen über '
    . 'before_standard_footer_html_generation auf zugelassenen normalen '
    . 'Moodle-Seiten. Ausgeschlossen sind Verwaltungs-, Anmelde-, '
    . 'Authentifizierungs-, Tool-, Manager- und Frontpage-Seitentypen, die '
    . 'Installation sowie embedded-, popup-, maintenance-, print- und '
    . 'redirect-Layouts. Anmeldung und Manager bleiben zur Wiederherstellung '
    . 'erreichbar. Ein Zeitpunkt nach DOMContentLoaded wird nicht garantiert. '
    . 'context enthält root, pageType, pageId, courseId und categoryId. Bei Moodles '
    . 'Inhaltsaktualisierungsereignis werden registrierte Snippets mit dem '
    . 'geänderten root erneut aufgerufen; schreibe wiederholbar initialisierbaren '
    . 'Code. JavaScript kann auf Seite und Netzwerk zugreifen. Synchrone Fehler '
    . 'werden protokolliert; Schleifen, asynchrone Fehler und bereits ausgeführte '
    . 'Änderungen sind nicht isoliert. Die alphabetische Auswahl wartet nicht auf '
    . 'asynchrone Arbeit des vorherigen Snippets.</p>');
$string['pagehelp_scriptexecution_help'] = ('Aktive veröffentlichte Snippets laufen über '
    . 'before_standard_footer_html_generation auf zugelassenen normalen '
    . 'Moodle-Seiten.');
$string['pagehelp_scriptidentity'] = 'Name und Kennung';
$string['pagehelp_scriptidentity_article'] = ('<p>Verwende einen verständlichen Namen und eine eindeutige Kennung aus '
    . 'Buchstaben, Ziffern, Unterstrichen oder Bindestrichen. Die Kennung benennt '
    . 'die Registrierung zur Laufzeit. Behalte sie bei bereits genutzten Skripten '
    . 'möglichst bei.</p>');
$string['pagehelp_scriptidentity_help'] = ('Verwende einen verständlichen Namen und eine eindeutige Kennung aus '
    . 'Buchstaben, Ziffern, Unterstrichen oder Bindestrichen.');
$string['pagehelp_scriptpublication'] = 'Entwurf, Veröffentlichung und Aktivstatus';
$string['pagehelp_scriptpublication_article'] = (
        '<p>Entwurf speichern verändert veröffentlichten Code nicht. Speichern und '
    . 'veröffentlichen erstellt eine unveränderliche Version. Der Aktivschalter '
    . 'gilt unabhängig davon. Deaktivierte Skripte werden künftig nicht mehr '
    . 'ausgeliefert; bereits ausgeführte oder gecachte Dateien können weiter '
    . 'wirken. Löschen erfordert eine Bestätigung und entfernt das Skript mit '
    . 'allen Versionen endgültig.</p>'
    );
$string['pagehelp_scriptpublication_help'] = 'Entwurf speichern verändert veröffentlichten Code nicht.';
$string['pagehelp_scriptrevisions'] = 'Versionen, Vergleich und Wiederherstellung';
$string['pagehelp_scriptrevisions_article'] = (
        '<p>Veröffentlichte Fassungen speichern Code, Notiz, Autor und Zeitpunkt. '
    . 'Vergleiche zwei Versionen oder sieh ihren Quelltext an. Wiederherstellen '
    . 'ersetzt den aktuellen Entwurf durch den gewählten Code. Prüfe ihn und '
    . 'veröffentliche ihn erneut bewusst; der Live-Stand wechselt beim '
    . 'Wiederherstellen nicht. Auswirkungen früher ausgeführten Codes werden '
    . 'dadurch nicht rückgängig gemacht.</p>'
    );
$string['pagehelp_scriptrevisions_help'] = 'Veröffentlichte Fassungen speichern Code, Notiz, Autor und Zeitpunkt.';
$string['pagehelp_scripts_title'] = 'Script Manager: Hilfe';
$string['pluginname'] = 'Script Manager';
$string['privacy:metadata:code'] = 'Vertrauenswürdiger JavaScript-Code';
$string['privacy:metadata:description'] = 'Beschreibung';
$string['privacy:metadata:name'] = 'Name';
$string['privacy:metadata:revisionnote'] = 'Revisionshinweis';
$string['privacy:metadata:scripts'] = 'Skriptentwürfe und Metadaten';
$string['privacy:metadata:scriptversions'] = 'Veröffentlichte Skriptrevisionen';
$string['privacy:metadata:userid'] = 'ID der bearbeitenden Person';
$string['publishrestoredrevision'] = 'Revision als neue Version veröffentlichen';
$string['publishscript'] = 'Aktuellen Entwurf veröffentlichen';
$string['revision'] = 'Version';
$string['revisionauthor'] = 'Autor/in';
$string['revisions'] = 'Versionen';
$string['saveandpublishscript'] = 'Speichern und veröffentlichen';
$string['savescriptdraft'] = 'Entwurf speichern';
$string['scriptcode'] = 'JavaScript';
$string['scriptdeleted'] = 'Das Skript wurde dauerhaft gelöscht.';
$string['scriptdescription'] = 'Beschreibung';
$string['scriptdescription_help'] = ('Eine optionale Erklärung für die Administration als Klartext. Sie wird '
    . 'nicht als Seiteninhalt ausgegeben.');
$string['scriptdiffaction'] = 'Mit vorheriger Revision vergleichen';
$string['scriptdiffaddedcharacters'] = '+&nbsp;{$a}&nbsp;Zeichen';
$string['scriptdiffblock'] = 'Der geänderte Bereich wird als zusammenhängender Block angezeigt.';
$string['scriptdiffcompare'] = 'Vergleichen';
$string['scriptdiffcountunavailable'] = 'Zeichenvergleich: Details';
$string['scriptdiffdense'] = 'Für Skripte mit sehr vielen Zeilen werden beide Revisionen vollständig angezeigt.';
$string['scriptdifffirstrevision'] = 'Erste Revision: Vergleich mit leerem Inhalt.';
$string['scriptdiffheading'] = 'Revisionen vergleichen';
$string['scriptdifflegend'] = '− Entfernt · + Hinzugefügt · Zeilenbereiche beziehen sich auf Ausgangs- und Zielrevision.';
$string['scriptdiffneedtwo'] = 'Für einen Vergleich werden zwei veröffentlichte Revisionen benötigt.';
$string['scriptdiffremovedcharacters'] = '-&nbsp;{$a}&nbsp;Zeichen';
$string['scriptdiffrevision'] = 'Revision {$a}';
$string['scriptdiffsource'] = 'Code der Revision {$a}';
$string['scriptdiffsummary'] = 'Änderungen';
$string['scriptdiffunchanged'] = 'Keine Codeänderungen zwischen diesen Revisionen.';
$string['scriptdiffviewrevision'] = 'Revision ansehen';
$string['scriptdraftsaved'] = 'Der Skriptentwurf wurde gespeichert.';
$string['scriptenabled'] = 'Aktiviert';
$string['scriptidentifier'] = 'Kennung';
$string['scriptidentifierexists'] = 'Diese Skriptkennung wird bereits verwendet.';
$string['scriptmanager:manage'] = 'Vertrauenswürdige JavaScript-Snippets verwalten';
$string['scriptmanagernotice'] = ('Skripte sind vertrauenswürdiger Administrationscode. Speichere einen Entwurf '
    . 'und veröffentliche ihn bewusst. Der Aktivstatus steuert künftige '
    . 'Auslieferungen; bereits ausgeführter oder gecachter Code wird dadurch nicht '
    . 'widerrufen.');
$string['scriptname'] = 'Skriptname';
$string['scriptname_help'] = 'Ein verständlicher Name für die Skriptliste. Die Auswahl erfolgt alphabetisch nach diesem Namen.';
$string['scriptpublication'] = 'Veröffentlichung';
$string['scriptpublished'] = 'Veröffentlichte Version vorhanden';
$string['scriptpublishedsaved'] = 'Eine neue unveränderliche Skriptversion wurde veröffentlicht.';
$string['scriptrevisionhistory'] = 'Veröffentlichungsverlauf des Skripts';
$string['scriptrevisionhistoryfor'] = 'Revisionen: {$a}';
$string['scriptrevisionhistorynotice'] = ('Das Laden einer Revision ersetzt den aktuellen Entwurf und öffnet ihn im '
    . 'Skripteditor. Sie wird nicht automatisch veröffentlicht; prüfen Sie sie '
    . 'dort und veröffentlichen Sie sie anschließend ausdrücklich als neue '
    . 'Revision.');
$string['scriptrevisionloaded'] = ('Revision {$a} wurde als aktueller Entwurf geladen. Es wurde noch nichts '
    . 'veröffentlicht. Prüfen oder bearbeiten Sie den Entwurf und verwenden Sie '
    . 'anschließend „Revision als neue Version veröffentlichen“.');
$string['scriptrevisionnote'] = 'Revisionshinweis';
$string['scriptsecuritynotice'] = ('<strong>Vertrauenswürdiges JavaScript:</strong> Der Code läuft mit den '
    . 'Browserrechten der jeweiligen Besucher. Prüfe ihn vor der Veröffentlichung '
    . 'und speichere hier keine Geheimnisse. context.root hilft beim gezielten '
    . 'Initialisieren aktualisierter Inhalte; es begrenzt die JavaScript-Rechte '
    . 'nicht.');
$string['scripttoolarge'] = 'Das Skript darf höchstens 500 KB groß sein.';
$string['scriptunpublished'] = 'Nur Entwurf';
$string['searchbardisplay'] = 'Darstellung';
$string['searchbarerror'] = 'Die Ergebnisse konnten nicht aktualisiert werden. Bitte die Suche erneut absenden.';
$string['searchbarfilters'] = 'Filtern';
$string['searchbarloading'] = 'Ergebnisse werden geladen …';
$string['searchbarpublication'] = 'Veröffentlichung';
$string['searchbarremove'] = 'Filter entfernen: {$a}';
$string['searchbarreset'] = 'Filter zurücksetzen';
$string['searchbarscripts'] = 'Namen, Kennungen und Beschreibungen durchsuchen …';
$string['tableheadingmodified'] = 'Geändert';
$string['tableheadingname'] = 'Name';
$string['tableheadingpublished'] = 'Publiziert';

$string['scriptdifffrom'] = 'Ausgangsversion';
$string['scriptdiffto'] = 'Zielversion';
$string['scriptactivated'] = 'Das Skript wurde aktiviert.';
$string['scriptdeactivated'] = 'Das Skript wurde deaktiviert.';
