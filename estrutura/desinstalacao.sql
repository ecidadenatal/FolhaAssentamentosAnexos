drop sequence plugins.assentamentoanexos_sequencial_seq;
drop table plugins.assentamentoanexos;

update configuracoes.db_itensmenu set funcao = 'rec1_assenta001.php' where id_item = 5578;
update configuracoes.db_itensmenu set funcao = 'rec1_assenta002.php' where id_item = 5579;
update configuracoes.db_itensmenu set funcao = 'rec1_assenta003.php' where id_item = 5580;

