# RACTUS
Rastreador de Condições de Temperatura e Umidade de Servidores

1.  Arduíno.
* Utilizamos o sensor DHT22 para ler a temperatura e umidade em um intervalo detempo definido;
* Realiza o registro na porta serial da máquina(servidor) conectada a ele.
2.  Python(Cliente).
* Executando na máquina conectada ao arduíno por meio de Thread, faz a leitura dosdados gravados na porta Serial;
* Adiciona aos dados a hora da leitura registrada;
* Formata para o padrão JSON e envia ao servidor pelo padrão REST.
3.  PHP(Servidor).•Recebe os dados do cliente Python;
* Armazena os dados no banco;
* Replica os dados em múltiplos bancos para ser tolerante a falhas; 
* Disponibiliza os dados para a interface gráfica por meio de API’s.
4.  React(Web).
* Obtém os dados por GET na API do servidor;
* Disponibiliza a temperatura e umidade mais atuais;
* Disponibiliza em gráfico a média de cada hora do dia;
* Disponibiliza em gráfico a média diária do último mês;
* Disponibiliza em tabela uma lista dos últimos registros lidos
