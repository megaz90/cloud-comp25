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

# Number Guesser Game

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

# Now that the rules are clear: Let the games begin and may the fastest player win - or the best fortune teller.

## Acknowledgments

This project was created by group CloudComp25 with the following members:

- team member-1
- team member-2
- team member-3
