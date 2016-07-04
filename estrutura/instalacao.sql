create table plugins.assentamentoanexos (sequencial integer, 
                                         assentamento integer, 
                                         arquivo varchar(200), 
                                         arquivooriginal varchar(200), 
                                         caminhoarquivo varchar(200), 
                                         data date, 
                                         ativo varchar(1));
create sequence plugins.assentamentoanexos_sequencial_seq;

update configuracoes.db_itensmenu set funcao = 'rec1_assenta004.php' where id_item = 5578;
update configuracoes.db_itensmenu set funcao = 'rec1_assenta005.php' where id_item = 5579;
update configuracoes.db_itensmenu set funcao = 'rec1_assenta006.php' where id_item = 5580;

                                          
