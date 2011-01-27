SELECT 
	COUNT( DISTINCT ifnull(ev.domain_id,"-")) count_all
FROM kalturadw.dwh_hourly_events_domain  ev ,
     (SELECT {TIME_SHIFT} time_shift, # time shift in hours
        {FROM_DATE_ID} start_date, # from date
        {TO_DATE_ID} end_date # to date
    ) p    
WHERE
{OBJ_ID_CLAUSE}
AND  ev.partner_id = {PARTNER_ID} /* PARTNER_ID*/
    AND date_id BETWEEN calc_time_shift(start_date, 0, time_shift) AND calc_time_shift(end_date, 23, time_shift)
    AND calc_time_shift(date_id, hour_id, time_shift) between p.start_date AND p.end_date
	AND 
( count_time_viewed > 0 OR
  count_plays > 0 OR
  count_loads > 0 )