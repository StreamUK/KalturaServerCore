SELECT 
	ev_stats.entry_id object_id,
	en.entry_name,
	count_plays,
	count_plays_25,
	count_plays_50,
	count_plays_75,
	count_plays_100,
	play_through_ratio
FROM
(
	SELECT 
		entry_id,
		SUM(count_plays) count_plays,
		SUM(count_plays_25) count_plays_25,
		SUM(count_plays_50) count_plays_50,
		SUM(count_plays_75) count_plays_75,
		SUM(count_plays_100) count_plays_100,
		( SUM(count_plays_100) / SUM(count_plays) ) play_through_ratio
	FROM 
		dwh_hourly_events_entry ev,
	     (SELECT {TIME_SHIFT} time_shift, # time shift in hours
            {FROM_DATE_ID} start_date, # from date
            {TO_DATE_ID} end_date # to date
        ) p
    WHERE 	{OBJ_ID_CLAUSE}
		AND partner_id =  {PARTNER_ID} # PARTNER_ID
        AND date_id BETWEEN calc_time_shift(p.start_date, 0, time_shift) AND calc_time_shift(p.end_date, 23, time_shift)
        AND calc_time_shift(date_id, hour_id, time_shift) between p.start_date AND p.end_date
		AND 
			( count_plays > 0 OR
			  count_plays_25 > 0 OR
			  count_plays_50 > 0 OR
			  count_plays_75 > 0 OR
			  count_plays_100 > 0 )
	GROUP BY entry_id
	ORDER BY {SORT_FIELD}
	LIMIT {PAGINATION_FIRST},{PAGINATION_SIZE}  /* pagination  */
) AS ev_stats, dwh_dim_entries en
WHERE ev_stats.entry_id = en.entry_id
