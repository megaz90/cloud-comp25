# Infrastructure Setup Script for Guessing Game Application

## Overview

This script automates the provisioning of infrastructure for the Guessing Game application using AWS services with AWS boto3 library. In order to interact with DynamoDB, AWS sdk for PHP was used by installing composer and php on web server instances.

It covers the following components:

- **EC2 Instances**: Creates web server instances.
- **Security Group**: Configures inbound rules for the instances.
- **Load Balancer**: Sets up a load balancer to distribute incoming traffic.
- **Auto Scaling Group**: Configures auto-scaling to manage the number of web server instances based on traffic.
- **DynamoDB Table**: Creates and manages the DynamoDB table used by the application.

## Prerequisites

**AWS Account**: An AWS account with necessary permissions to create and manage EC2 instances, security groups, load balancers, auto-scaling groups, and DynamoDB tables.

**IAM**: Python script and PHP application is coded in a way where <b>IAM</b> has permission to create EC2 instance as well as has permission to create DynamoDB tables since we can't access AWS credentials with free tier.

## Auto Scalability

- **Minimum / Default Number of Instances:** The ASG is configured to start with a minimum of 1 EC2 instance to handle the initial traffic load.
- **Maximum Number of Instances:** As traffic increases, the ASG can scale up to 3 EC2 instances. This ensures that the application can handle higher loads.
- **Scaling Policy:** The ASG uses a scaling policy that monitors the number of requests per instance. If the requests exceed 5 requests per instance, the ASG will automatically add more instances, up to the maximum limit.

## Usage

1. **Initialize AWS Clients**: The script initializes clients for EC2, Elastic Load Balancing (ELB), Auto Scaling, and DynamoDB.
2. **Clean Up Old Resources**: It deletes any existing configurations, including old instances, auto-scaling groups, load balancers, security groups, and DynamoDB tables.
3. **Create New Resources**: It creates new DynamoDB tables, security groups, EC2 instance, a load balancer, and auto-scaling groups.
4. **Output**: The script prints out the link for load balancers where application is running.
5. **When to access Application**: Wait at least 5-10 minutes for the load balancers to run completely and then try accessing the application.

## Running the Script

To run the script, go to the directory and execute the following command:

```
python start.py
```

## Stopping the Script

To stop/delete the running resources use stop script. You can execute with the following command:

```
python stop.py
```

## Notes

- This script is based on the script used in labs. It is modified version of boto3 script used in the labs.

# Number Guessing Game

## PHP Application Structure

The Guessing Game web application is built with **PHP** and interacts with **AWS DynamoDB** to manage game data. The application is structured as follows:

### Directory Structure

```
guessing-in-cloud/
  ├── web-content/
    ├── src/
    │   ├── index.php
    │   ├── game.php
    │   ├── config.php
    │   ├── functions.php
    │   ├── process_game.php
    │   ├── process_player.php
  ├── composer.json
  ├── .htaccess
```

### File Descriptions

- **index.php**: The entry point of the application, presenting the user interface for the guessing game.
- **game.php**: Contains the logic for managing the game flow and interactions.
- **config.php**: Holds configuration settings, including DynamoDB client initialization and table information.
- **functions.php**: Contains helper functions for interacting with DynamoDB, game logic, and data processing.
- **process_game.php**: Handles creation of game and direct request to respective functions.
- **process_player.php**: Handles creation of player session of games.
- **composer.json**: Contains all application dependencies.

### DynamoDB Integration

The Guessing Game application uses **AWS DynamoDB** to store game and player data. The interactions with DynamoDB are handled via the **AWS SDK for PHP**, which is installed using **Composer**.

- **Game Data Storage**: Each game session is stored in the `cloud_guessing_game` DynamoDB table.
- **Data Access**: The PHP scripts use helper functions defined in `functions.php` to interact with the DynamoDB table. These functions include creating, reading, and updating game and player records.

### .htaccess File

An `.htaccess` file is configured to handle URL rewriting and manage PHP routing. Additionally, it allows the Guessing Game application to set the `index.php` file as the default page and control the behavior of the application.

## Welcome to Number Guesser Game!

The rules of the game are simple: Players have to simultaneously guess a number. Whoever guesses the number correctly first wins the game.

## How To play Guessing Game

1. **Starting a New Game:**

   - Visit the landing page.
   - To start a new game, fill out the form at the bottom of the landing page with the name of the game, the maximum number, and the password 'password' (pshhh, don't tell this to any malicious person).
   - The number that has to be guessed lies between 0 and the number specified as the maximum number. It will be a new random number for every new game (unless the game was rigged :O).

2. **Joining a Game:**

   - To enter a game, visit the landing page simply click on the name of the game in the list and state who you are.

3. **Playing the Game:**

   - Enter your guess in the provided form.

4. **Winning the Game:**
   - The first player to guess the correct number wins.

## Now that the rules are clear: Let the games begin and may the fastest player win - or the best fortune teller.

## Acknowledgments

This project was created by group CloudComp25 group with the following members:

- team member-1
- team member-2
- team member-3
- team member-4
