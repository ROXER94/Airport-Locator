#Galton Board - Demonstration of the Central Limit Theorem

#The height of the Galton Board
height = 10
#The number of balls used
attempts = 20000

import random

output = [0] * (height+1)

#The path the ball takes going down a Galton Board
def drop():
	path=""
	for a in range(0,height):
		direction = random.choice("LR")
		path += direction
	return path;

#The final location of the ball on the Galton Board
def location(str):
	zone = [0] * (height+1)
	L = str.count("L")
	R = str.count("R")
	if L - R > 0:
		total = L - R
		zone[int(height/2-total/2)] += 1
	elif R - L > 0:
		total = R - L
		zone[int(height/2+total/2)] += 1
	else:
		zone[int(height/2)] += 1
	return zone;

while attempts > 0:
	output = [x + y for x, y in zip(output, location(drop()))]
	attempts -= 1
	
print(output)
#Shows a Normal Distribution