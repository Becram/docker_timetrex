FROM postgres:9.6

ENV APP_ROOT /src
WORKDIR ${APP_ROOT}
#RUN mkdir -p  /script && > /etc/cron.d/pg_backup-cron && echo "*/5 * * * * /script/autopgsqlbackup" >> /etc/cron.d/pg_backup-cron
ADD autopgsqlbackup /script/
#RUN localedef -i en_GB -c -f UTF-8 -A /usr/share/locale/locale.alias en_GB.UTF-8
#ENV LANG en_GB.utf8

RUN chmod +x /script/autopgsqlbackup
