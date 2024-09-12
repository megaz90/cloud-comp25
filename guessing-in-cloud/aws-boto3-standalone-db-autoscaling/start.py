import time

import boto3
from botocore.exceptions import ClientError


################################################################################################
#
# Configuration Parameters
#
################################################################################################

# changed to use us-east, to be able to use AWS Educate Classroom
region = 'us-east-1'
availabilityZone1 = 'us-east-1a'
availabilityZone2 = 'us-east-1b'
availabilityZone3 = 'us-east-1c'


# AMI ID of Amazon Linux 2 image 64-bit x86 in us-east-1 (can be retrieved, e.g., at
# https://console.aws.amazon.com/ec2/v2/home?region=us-east-1#LaunchInstanceWizard:)
# TODO update to recent version of Amazon Linux 2 AMI?
imageId = 'ami-0d5eff06f840b45e9'
# for eu-central-1, AMI ID of Amazon Linux 2 would be:
# imageId = 'ami-0cc293023f983ed53'

instanceType = 't2.micro'
keyName = 'vockey'
iamRole = 'LabInstanceProfile'


################################################################################################
#
# boto3 code
#
################################################################################################

# Initialize clients
client = boto3.setup_default_session(region_name=region)
ec2Client = boto3.client("ec2")
ec2Resource = boto3.resource('ec2')
elbv2Client = boto3.client('elbv2')
asClient = boto3.client('autoscaling')

# Initialize a DynamoDB client
dynamodb = boto3.resource('dynamodb', region_name='us-east-1')

# VPC, Subnet, and Security Group Setup
response = ec2Client.describe_vpcs()
vpc_id = response.get('Vpcs', [{}])[0].get('VpcId', '')

subnet_id1 = ec2Client.describe_subnets(Filters=[{'Name': 'availability-zone', 'Values': [availabilityZone1]}])['Subnets'][0]['SubnetId']
subnet_id2 = ec2Client.describe_subnets(Filters=[{'Name': 'availability-zone', 'Values': [availabilityZone2]}])['Subnets'][0]['SubnetId']
subnet_id3 = ec2Client.describe_subnets(Filters=[{'Name': 'availability-zone', 'Values': [availabilityZone3]}])['Subnets'][0]['SubnetId']

# Function to create a DynamoDB table if it doesn't exist
def create_dynamodb_table():
    try:
        table = dynamodb.create_table(
            TableName='cloud_guessing_game',
            KeySchema=[{'AttributeName': 'cloud_id', 'KeyType': 'HASH'}],
            AttributeDefinitions=[{'AttributeName': 'cloud_id', 'AttributeType': 'N'}],
            ProvisionedThroughput={'ReadCapacityUnits': 5, 'WriteCapacityUnits': 5}
        )
        table.wait_until_exists()
        print(f"DynamoDB Table '{table.table_name}' created successfully.")
    except ClientError as e:
        if 'ResourceInUseException' in str(e):
            print("DynamoDB Table already exists. Skipping creation.")
        else:
            raise e

# Function to insert data into DynamoDB table
def insert_instance_data(instance_id, instance_type, private_ip, status):
    table = dynamodb.Table('cloud_guessing_game')
    try:
        table.put_item(
            Item={
                'cloud_id': int(instance_id[-5:], 16),  # Generate cloud_id based on instance_id (for uniqueness)
                'instance_id': instance_id,
                'instance_type': instance_type,
                'private_ip': private_ip,
                'status': status
            }
        )
        print(f"Inserted instance data for {instance_id} into DynamoDB.")
    except ClientError as e:
        print(f"Error inserting instance data: {e}")
        

#Deleting old configurations
print("Deleting old auto scaling group...")
print("------------------------------------")

try:
    response = asClient.delete_auto_scaling_group(AutoScalingGroupName='guessing-game-asg-autoscalinggroup', ForceDelete=True)
except ClientError as e:
    print(e)

print("Deleting old launch configuration...")
print("------------------------------------")

try:
    response = asClient.delete_launch_configuration(LaunchConfigurationName='guessing-game-asg-launchconfig')
except ClientError as e:
    print(e)



print("Deleting old instances...")
print("------------------------------------")

response = ec2Client.describe_instances(Filters=[{'Name': 'tag-key', 'Values': ['guessing-game-asg']}])
print(response)
reservations = response['Reservations']
for reservation in reservations:
    for instance in reservation['Instances']:
        if instance['State']['Name'] == "running":
            response = ec2Client.terminate_instances(InstanceIds=[instance['InstanceId']])
            print(response)
            instanceToTerminate = ec2Resource.Instance(instance['InstanceId'])
            instanceToTerminate.wait_until_terminated()


print("Deleting old load balancer and deps...")
print("------------------------------------")

try:
    response = elbv2Client.describe_load_balancers(Names=['guessing-game-asg-loadbalancer'])
    loadbalancer_arn = response.get('LoadBalancers', [{}])[0].get('LoadBalancerArn', '')
    response = elbv2Client.delete_load_balancer(LoadBalancerArn=loadbalancer_arn)

    waiter = elbv2Client.get_waiter('load_balancers_deleted')
    waiter.wait(LoadBalancerArns=[loadbalancer_arn])
except ClientError as e:
    print(e)

try:
    response = elbv2Client.describe_target_groups(Names=['guessing-game-asg-targetgroup'])
    while len(response.get('TargetGroups', [{}])) > 0:
        targetgroup_arn = response.get('TargetGroups', [{}])[0].get('TargetGroupArn', '')
        try:
            response = elbv2Client.delete_target_group(TargetGroupArn=targetgroup_arn)
        except ClientError as e:
            print(e)
        response = elbv2Client.describe_target_groups(Names=['guessing-game-asg-targetgroup'])
        time.sleep(5)
except ClientError as e:
    print(e)

print("Delete old security group...")
print("------------------------------------")

try:
    response = ec2Client.describe_security_groups(Filters=[{'Name': 'group-name', 'Values': ['guessing-game-asg']}])
    while len(response.get('SecurityGroups', [{}])) > 0:
        security_group_id = response.get('SecurityGroups', [{}])[0].get('GroupId', '')
        try:
            response = ec2Client.delete_security_group(GroupName='guessing-game-asg')
        except ClientError as e:
            print(e)
        response = ec2Client.describe_security_groups(Filters=[{'Name': 'group-name', 'Values': ['guessing-game-asg']}])
        time.sleep(5)
except ClientError as e:
    print(e)

print("Create Dynamo DB tables")
print("------------------------------------")
# Creating DynamoDB table (if it doesn't exist)
create_dynamodb_table()

print("Create security group...")
print("------------------------------------")

try:
    response = ec2Client.create_security_group(GroupName='guessing-game-asg',
                                               Description='guessing-game-asg',
                                               VpcId=vpc_id)
    security_group_id = response['GroupId']
    print('Security Group Created %s in vpc %s.' % (security_group_id, vpc_id))

    data = ec2Client.authorize_security_group_ingress(
        GroupId=security_group_id,
        IpPermissions=[
            {'IpProtocol': 'tcp',
             'FromPort': 3306,
             'ToPort': 3306,
             'IpRanges': [{'CidrIp': '0.0.0.0/0'}]},
            {'IpProtocol': 'tcp',
             'FromPort': 22,
             'ToPort': 22,
             'IpRanges': [{'CidrIp': '0.0.0.0/0'}]},
            {'IpProtocol': 'tcp',
             'FromPort': 80,
             'ToPort': 80,
             'IpRanges': [{'CidrIp': '0.0.0.0/0'}]},
            {'IpProtocol': 'tcp',
             'FromPort': 443,
             'ToPort': 443,
             'IpRanges': [{'CidrIp': '0.0.0.0/0'}]}
        ])
    print('Ingress Successfully Set %s' % data)
except ClientError as e:
    print(e)

print("Running new DB instance...")
print("------------------------------------")

userDataDB = ('#!/bin/bash\n'
              'yum install -y joe htop git\n'
              )

response = ec2Client.run_instances(
    ImageId=imageId,
    InstanceType=instanceType,
    Placement={'AvailabilityZone': availabilityZone1, },
    KeyName=keyName,
    MinCount=1,
    MaxCount=1,
    UserData=userDataDB,
    SecurityGroupIds=[security_group_id,],
    TagSpecifications=[
        {
            'ResourceType': 'instance',
            'Tags': [
                {'Key': 'Name', 'Value': 'guessing-game-asg-db1'},
                {'Key': 'guessing-game-asg', 'Value': 'db'}
            ],
        }
    ],
)

instanceIdDB = response['Instances'][0]['InstanceId']
privateIpDB = response['Instances'][0]['PrivateIpAddress']
instance = ec2Resource.Instance(instanceIdDB)
instance.wait_until_running()

print(instanceIdDB)

userDataWebServer = ('#!/bin/bash\n'
                     '# essential tools\n'
                     'yum install -y joe htop git\n'
                     '# Install AWS CLI\n'
                     'yum install -y aws-cli\n'
                     '# Install Apache Web Server\n'
                     'yum install -y httpd\n'
                     '# Start Apache Web Server\n'
                     'service httpd start\n'
                     '\n'
                     'cd /var/www/html\n'
                     'wget https://raw.githubusercontent.com/megaz90/cloud-comp25/main/guessing-in-cloud/web-content/src/index.php\n'
                     'wget https://raw.githubusercontent.com/megaz90/cloud-comp25/main/guessing-in-cloud/web-content/src/cloud.php\n'
                     'wget https://raw.githubusercontent.com/megaz90/cloud-comp25/main/guessing-in-cloud/web-content/src/config.php\n'
                     '\n'
                     '# change hostname of db connection\n'
                     'sed -i s/localhost/' + privateIpDB + '/g /var/www/html/config.php\n'
                     )

print("Creating launch configuration...")
print("------------------------------------")

response = asClient.create_launch_configuration(
    #IamInstanceProfile='my-iam-role',
    IamInstanceProfile=iamRole,
    ImageId=imageId,
    InstanceType=instanceType,
    LaunchConfigurationName='guessing-game-asg-launchconfig',
    UserData=userDataWebServer,
    KeyName=keyName,
    SecurityGroups=[
        security_group_id,
    ],
)

elbv2Client = boto3.client('elbv2')

print("Creating load balancer...")
print("------------------------------------")

response = elbv2Client.create_load_balancer(
    Name='guessing-game-asg-loadbalancer',
    Subnets=[
        subnet_id1,
        subnet_id2,
        subnet_id3,
    ],
    SecurityGroups=[
        security_group_id
    ]
)

loadbalancer_arn = response.get('LoadBalancers', [{}])[0].get('LoadBalancerArn', '')
loadbalancer_dns = response.get('LoadBalancers', [{}])[0].get('DNSName', '')

print("Creating target group...")
print("------------------------------------")

response = elbv2Client.create_target_group(
    Name='guessing-game-asg-targetgroup',
    Port=80,
    Protocol='HTTP',
    VpcId=vpc_id,
)

targetgroup_arn = response.get('TargetGroups', [{}])[0].get('TargetGroupArn', '')

print("Creating listener...")
print("------------------------------------")

response = elbv2Client.create_listener(
    DefaultActions=[
        {
            'TargetGroupArn': targetgroup_arn,
            'Type': 'forward',
        },
    ],
    LoadBalancerArn=loadbalancer_arn,
    Port=80,
    Protocol='HTTP',
)

response = elbv2Client.modify_target_group_attributes(
    TargetGroupArn=targetgroup_arn,
    Attributes=[
        {
            'Key': 'stickiness.enabled',
            'Value': 'true'
        },
    ]
)

print("Creating auto scaling group...")
print("------------------------------------")

response = asClient.create_auto_scaling_group(
    AutoScalingGroupName='guessing-game-asg-autoscalinggroup',
    LaunchConfigurationName='guessing-game-asg-launchconfig',
    MaxSize=3,
    MinSize=1,
    HealthCheckGracePeriod=120,
    HealthCheckType='ELB',
    TargetGroupARNs=[
        targetgroup_arn,
    ],
    VPCZoneIdentifier=subnet_id1 + ', ' + ', ' + subnet_id2 + ', ' + subnet_id3,
    Tags=[
        {'Key': 'Name', 'Value': 'guessing-game-asg-webserver', 'PropagateAtLaunch': True},
        {'Key': 'guessing-game', 'Value': 'webserver', 'PropagateAtLaunch': True}
    ],
)

print(loadbalancer_arn)
print(targetgroup_arn)
print('app/guessing-game-asg-loadbalancer/'+str(loadbalancer_arn).split('/')[3]+'/targetgroup/guessing-game-asg-targetgroup/'+str(targetgroup_arn).split('/')[2])

print('If target group is not found, creation was delayed in AWS Academy lab, need to add a check that target group is'
      'existing before executing the next lines in the future... If the error occurs, rerun script...')

response = asClient.put_scaling_policy(
    AutoScalingGroupName='guessing-game-asg-autoscalinggroup',
    PolicyName='guessing-game-asg-scalingpolicy',
    PolicyType='TargetTrackingScaling',
    EstimatedInstanceWarmup=60,
    TargetTrackingConfiguration={
        'PredefinedMetricSpecification': {
            'PredefinedMetricType': 'ALBRequestCountPerTarget',
            'ResourceLabel': 'app/guessing-game-asg-loadbalancer/'+str(loadbalancer_arn).split('/')[3]+'/targetgroup/guessing-game-asg-targetgroup/'+str(targetgroup_arn).split('/')[2]
        },
        'TargetValue': 5.0,
    }
)

# Example of querying DynamoDB to check instance data
def query_instance_data(instance_id):
    table = dynamodb.Table('cloud_guessing_game')
    try:
        response = table.get_item(Key={'cloud_id': int(instance_id[-5:], 16)})
        if 'Item' in response:
            print(f"Queried instance data: {response['Item']}")
        else:
            print(f"No data found for instance {instance_id}")
    except ClientError as e:
        print(f"Error querying instance data: {e}")

# Example of querying the instance data
query_instance_data(instanceIdDB)

print('Load Balancer should be reachable at: http://' + loadbalancer_dns)
