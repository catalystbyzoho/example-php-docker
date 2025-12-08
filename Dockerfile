# Use PHP Alpine image for lightweight container
FROM php:8.2-alpine

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY index.php .

# Expose port 8000
EXPOSE 8000

# Start PHP built-in server
CMD ["php", "-S", "0.0.0.0:8000"]