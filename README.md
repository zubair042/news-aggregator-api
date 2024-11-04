<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

## News Aggregator API

The News Aggregator API is a RESTful API built with Laravel that allows users to aggregate articles from various news sources. It includes user authentication, article management, personalized news feeds, and more.

## Features

- **User Authentication**: Registration, login, logout, and password reset using Laravel Sanctum for token-based authentication.
- **Article Management**:
  - Fetch articles with pagination.
  - Search articles by keyword, date, category, and source.
  - Retrieve detailed information about individual articles.
- **User Preferences**: 
  - Manage preferences for news sources, categories, and authors.
  - Access a personalized news feed based on preferences.
- **Data Aggregation**:
  - Schedule commands to fetch articles from selected news APIs and store them in the local database.

## Installation

### Prerequisites
- Docker
    - Download and Install: Visit the Docker official page, select your OS, and follow the installation steps.
    - Verify Installation: Open a terminal and run:
       ```bash
       docker --version


### Setup Instructions

1. **Clone the repository**:
   - Clone the repo
   ```bash
   cd news-aggregator-api

2. **Configure the .env file:**:
    - After cloning the repository, open the .env file in the project’s root directory and add your API keys for news sources:
      ```plaintext
      NEWS_API_KEY={Your News API Key}
      GUARDIAN_API_KEY={Your Guardian API Key}
      NYTIMES_API_KEY={Your New York Times API Key}
      ```
   
3. **Build and run the Docker containers:**:
   ```bash
   docker-compose up --build -d
   
4. **Access the application:**:
   - Open your browser and navigate to **http://localhost:9001**
     
5. **Access the database:**:
   - Open your browser and navigate to **http://localhost:8080**
   - Use the following credentials:
       - Username: **root**
       - Password: **123**
         
6. **Fetch News Articles from APIs:**:
   - Access the container:
       ```bash
       docker ps -a
       docker exec -it <container-id or container-name> bash
   - Run the following command to fetch articles from news sources:
       ```bash
       php artisan fetch:articles
   - This will insert article records into the articles table
     
4. **For API documentation, visit:**:
- Access all APIs at: **http://localhost:9001/api/documentation**
- Register or log in to obtain a token.
- Paste the token in the Swagger authorization section to access the API records.



## Feature Testing

To execute the feature tests:

1. **Create a testing database**:
   - Set up a new database in phpmyadmin.
     
2. **Update the `.env.testing` file**:
   - Configure it to use the testing database:
     ```plaintext
     DB_CONNECTION=mysql
     DB_DATABASE=your_testing_database
     DB_USERNAME=root
     DB_PASSWORD=123
     ```
3. **Access the application container**:
   ```bash
   docker ps -a
   docker exec -it <app-container-name or app-container-id> bash
   
4. **Access the application container**:
   ```bash
   php artisan test


## Conclusion
The News Aggregator API offers a robust and customizable platform for aggregating and managing articles from multiple sources. With features such as user-specific preferences, comprehensive article search, and efficient data aggregation, it provides an efficient solution for users seeking consolidated news access. Contributions are always welcome, as enhancements and optimizations will continue to improve the functionality and usability of the API.



