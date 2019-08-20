update character_sentences set status = 'executed' where imprisonment_start is not null
and imprisonment_end < unix_timestamp() 
and status = 'executing';