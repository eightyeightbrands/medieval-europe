select count(user_id), user_id from characters 
group by user_id 
having count(user_id) > 1

7077
7081
7095

update characters set health=0, glut=0 where id = 6389;
update characters set health=0, glut=0 where id = 6406;

select * from character_actions where character_id not in ( select id from characters );

select s.id, s.character_id, st.type from structures s, structure_types st 
where s.structure_type_id = st.id 
and character_id not in ( select id from characters )
and st.type in ( 'house', 'shop', 'terrain' ); 

-- fix strutture con character id che non esistono...

delete from structures  
where character_id not in ( select id from characters ) 
and structure_type_id in (select id from structure_types where type in ( 'house', 'shop', 'terrain' ) );




