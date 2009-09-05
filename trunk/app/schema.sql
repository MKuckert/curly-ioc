create table news (
  id int(10) unsigned not null auto_increment,
  title varchar(100) not null,
  content text not null,
  primary key(id)
);

insert into news (id, title, content) values
(1, 'Suicide bomber injures German soldiers in Afghanistan', 'Four German soldiers have been injured in a suicide bomb blast in northern Afghanistan, one day after a NATO air strike killed dozens of Taliban insurgents, as well as civilians.'),
(2, 'Anti-nuclear rally in Berlin becomes campaign issue', 'Thousands of people from across Germany, including farmers on tractors, are in Berlin this Saturday to protest against the nuclear energy industry and in support of a plan to shut down the country''s nuclear reactors.');