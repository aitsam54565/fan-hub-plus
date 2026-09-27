<?php
require_once __DIR__.'/../includes/functions.php';
$event=db_one('SELECT * FROM events WHERE event_id=?',[(int)get('id')]);if(!$event) fail(404,'Event not found.');
function ics_text(string $text): string { return str_replace(["\\","\r","\n",';',','],['\\\\','','\\n','\\;','\\,'],$text); }
function ics_line(string $line): string {
    $result='';while(strlen($line)>70){$chunk=mb_strcut($line,0,70,'UTF-8');$result.=$chunk."\r\n ";$line=substr($line,strlen($chunk));}return $result.$line."\r\n";
}
header('Content-Type: text/calendar; charset=utf-8');header('Content-Disposition: attachment; filename="event-'.(int)$event['event_id'].'.ics"');
$lines=['BEGIN:VCALENDAR','VERSION:2.0','PRODID:-//Fan Hub Plus//Events//EN','BEGIN:VEVENT','UID:event-'.$event['event_id'].'@fanhubplus.local','DTSTAMP:'.gmdate('Ymd\THis\Z'),'SUMMARY:'.ics_text($event['title']),'DESCRIPTION:'.ics_text(($event['description']??'').($event['ticket_url']?"\n".$event['ticket_url']:'')),'LOCATION:'.ics_text(implode(', ',array_filter([$event['venue'],$event['city'],$event['country']])))];
$date=str_replace('-','',$event['event_date']);
if($event['start_time']) {$lines[]='DTSTART:'.$date.'T'.str_replace(':','',$event['start_time']);if($event['end_time'])$lines[]='DTEND:'.$date.'T'.str_replace(':','',$event['end_time']);}
else {$lines[]='DTSTART;VALUE=DATE:'.$date;$lines[]='DTEND;VALUE=DATE:'.(new DateTime($event['event_date']))->modify('+1 day')->format('Ymd');}
$lines[]='END:VEVENT';$lines[]='END:VCALENDAR';foreach($lines as $line) echo ics_line($line);
