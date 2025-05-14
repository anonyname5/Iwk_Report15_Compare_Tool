FROM nginx:alpine

# Install necessary utilities
RUN apk add --no-cache wget curl bash

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf

# Create the public directory
RUN mkdir -p /var/www/html/public

# Create a health check endpoint
RUN mkdir -p /var/www/html/public && \
    echo "OK" > /var/www/html/public/ping

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"] 