FROM        debian
MAINTAINER  Bikram Dhoju <bikram.dhoju@olenepal.org>

# Update the package repository
RUN DEBIAN_FRONTEND=noninteractive apt-get update && \ 
	DEBIAN_FRONTEND=noninteractive apt-get upgrade -y && \
	DEBIAN_FRONTEND=noninteractive apt-get install -y wget curl locales

#Set timezone
ENV TZ=Asia/Kathmandu
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone


RUN export LANGUAGE=en_US.UTF-8 && \
	export LANG=en_US.UTF-8 && \
	export LC_ALL=en_US.UTF-8 && \
	locale-gen en_US.UTF-8 && \
	DEBIAN_FRONTEND=noninteractive dpkg-reconfigure locales
# Added dotdeb to apt
#RUN echo "deb http://packages.dotdeb.org wheezy-php55 all" >> /etc/apt/sources.list.d/dotdeb.org.list && \
#	echo "deb-src http://packages.dotdeb.org wheezy-php55 all" >> /etc/apt/sources.list.d/dotdeb.org.list && \
#	wget -O- http://www.dotdeb.org/dotdeb.gpg | apt-key add -

# Install PHP 7.0
RUN apt-get update; apt-get install -y apache2 php libapache2-mod-php php7.0-cgi php7.0-cli php7.0-pgsql php7.0-pspell php7.0-gd php7.0-gettext php7.0-imap php7.0-intl php7.0-json php7.0-soap php7.0-zip php7.0-mcrypt php7.0-curl php7.0-ldap php7.0-xml php7.0-xsl php7.0-mbstring php7.0-bcmath 
 

# Activate a2enmod
RUN a2enmod rewrite
RUN mkdir -p /var/run/apache2
ADD ./001-docker.conf /etc/apache2/sites-enabled/000-default.conf
#RUN ln -s /etc/apache2/sites-available/001-docker.conf /etc/apache2/sites-enabled/

# Set Apache environment variables (can be changed on docker run with -e)
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid
ENV APACHE_RUN_DIR /var/run/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_SERVERADMIN admin@localhost
ENV APACHE_SERVERNAME localhost
ENV APACHE_SERVERALIAS docker.localhost
ENV APACHE_DOCUMENTROOT /var/www/html/timetrex


RUN rm -rf /var/www/html/*.html
RUN mkdir ${APACHE_DOCUMENTROOT}
#COPY ./timetrex_files ${APACHE_DOCUMENTROOT}/timetrex
RUN chmod -R 775 ${APACHE_DOCUMENTROOT}
RUN chown -R ${APACHE_RUN_USER}:${APACHE_RUN_GROUP} ${APACHE_DOCUMENTROOT}

RUN mkdir -p /var/log/timetrex
RUN chmod -R 775 /var/log/timetrex
RUN chown -R ${APACHE_RUN_USER}:${APACHE_RUN_GROUP} /var/log/timetrex

RUN mkdir -p /var/timetrex/storage
RUN chmod -R 775 /var/timetrex/storage
RUN chown -R ${APACHE_RUN_USER}:${APACHE_RUN_GROUP} /var/timetrex/storage


EXPOSE 80
ADD start.sh /start.sh
RUN chmod 0755 /start.sh
CMD ["bash", "start.sh"]
