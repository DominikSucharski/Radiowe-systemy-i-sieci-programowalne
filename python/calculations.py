#!/home/agnieszka/Documents/studia/radiowe/env/bin/python3.7
import geopy.distance
from math import *
#import matplotlib.pyplot as plt
import numpy as np
import itertools
import json
import random
import ast
from math import *

#import matplotlib
#matplotlib.use( 'tkagg' )


class Calculation():

    def __init__(self, coords_user, matrix, list_of_trensmitters, transmitting_power, channel, bandwidth,
                 carrier_frequency, aclr_1, aclr_2):
        self.channel = channel
        self.transmitting_power = transmitting_power
        self.matrix = matrix
        self.accept = False
        self.points = None
        self.bandwidth = bandwidth
        self.carrier_frequency = carrier_frequency
        self.aclr_1 = aclr_1
        self.aclr_2 = aclr_2
        self.noise_transmitter = self.thermal_noise_power()
        # print("list transmitters: ", list_of_trensmitters)
        # print("coord x user:" , coords_user)
        for transmitter in list_of_trensmitters:
            transmitter['points'] = ast.literal_eval(transmitter['points'])

            # print("type transmitter['coord_x']: ", type(transmitter['coord_x']))
            # print("type transmitter['coord_y']: ", type(transmitter['coord_y']))
            # print("type transmitter['channel']: ", type(transmitter['channel']))
            # print("type transmitter['power']: ", type(transmitter['power']))
            # print("type transmitter['points']: ", type(transmitter['points']))

        if not list_of_trensmitters:
            self.points, radius = self.calculate_snr_for_points(coords_user)
            # list_of_trensmitters.append({"coord_x": coords_user[0], "coord_y": coords_user[1], "channel": channel, "power": transmitting_power, "radius": radius,
            #          "points": self.points})
            # self.show_map(list_of_trensmitters)
            self.accept = True
        else:
            self.accept = self.check_sinr_for_points_other_transmitters(coords_user, list_of_trensmitters)
            if self.accept:
                self.points, radius = self.calculate_snr_for_points(coords_user)
                # list_of_trensmitters.append(
                #     {"coord_x": coords_user[0], "coord_y": coords_user[1], "channel": channel,
                #      "power": transmitting_power, "radius": radius,
                #      "points": self.points})
                # self.show_map(list_of_trensmitters)

    def is_accept(self):
        print("accept: ", self.accept)
        if self.accept:
            return self.accept, list(self.points)
        else:
            return self.accept, []

    def show_map(self, transmitters):
        fig, ax = plt.subplots()
        plt.grid(linestyle='--')
        ax.set(xlim=(0, 200), ylim=(0, 200))
        list_of_colors = [(0, 0, 0.5), (0, 0.5, 0), (0.5, 0, 0), (0.5, 0.5, 0), (0, 0.5, 0.5), (0.5, 0, 0.5),
                          (0.5, 0.5, 0.5)]
        for i, transmitter in enumerate(transmitters):
            # r = random.random()
            # g = random.random()
            # b = random.random()
            a_circle = plt.Circle((transmitter["coord_x"], transmitter["coord_y"]), int(transmitter["radius"]),
                                  color=list_of_colors[i], alpha=0.5)
            ax.add_artist(a_circle)
            # ax.add_artist(a_circle_2)
        plt.show()

    def calculate_snr_for_points(self, coords_transmitter):
        points_snr_greater_than_6dB = []
        distances = []
        for point in self.matrix:
            distance = self.calculateDistance(point, coords_transmitter)
            if distance != 0:
                fsl_dB = self.free_space_loss(distance)
                # print("fsl: ", fsl_dB)
                thermal_noise_power_dBm = self.thermal_noise_power()
                # print("thermal noise: ", thermal_noise_power_dBm)
                PRX = self.transmitting_power - fsl_dB
                snr = self.SNR(PRX, thermal_noise_power_dBm)
                # print("snr: ", snr)
                if snr > 6:
                    # print("distance: ", distance)
                    # print("snr: ", snr)
                    distances.append(distance)
                    points_snr_greater_than_6dB.append(point)

        # fig, ax = plt.subplots()
        # plt.grid(linestyle='--')
        # ax.set(xlim=(0, 10000), ylim=(0, 10000))
        # plt.title("SNR map")
        # plt.xlabel("latidue")
        # plt.ylabel("longtidue")
        # d = []
        # for i, point in enumerate(points_snr_greater_than_6dB):
        #     d.append(self.calculateDistance(point, coords_transmitter))
        #     plt.scatter(point[0], point[1], s=6, color='blue')
        # print("max: ", max(d))
        # plt.show()
        if not distances:
            return points_snr_greater_than_6dB, 0
        return points_snr_greater_than_6dB, max(distances)

    def check_sinr_for_points_other_transmitters(self, coords_transmitter, list_of_trensmitters):
        for transmitter in list_of_trensmitters:
            other_transmitters = list_of_trensmitters.copy()
            other_transmitters.remove(transmitter)
            for transmitter_point in transmitter["points"]:
                interference = 0
                if other_transmitters:
                    for other_transmitter in other_transmitters:
                        distance = self.calculateDistance(transmitter_point,
                                                          (other_transmitter["coord_x"], other_transmitter["coord_y"]))
                        if distance != 0:
                            fsl_dB_other_transmitter = self.free_space_loss(distance)
                            transmitter_power = other_transmitter['power']
                            if other_transmitter['channel'] != transmitter['channel']:
                                if other_transmitter['channel'] == transmitter['channel'] + 1 or other_transmitter['channel'] == transmitter['channel'] - 1:
                                    transmitter_power -= other_transmitter["aclr_1"]
                                    PRX_dBm_other_transmitter = transmitter_power - fsl_dB_other_transmitter
                                    PRX_mW = self.dBm_to_mW(PRX_dBm_other_transmitter)
                                    interference = interference + PRX_mW

                                if other_transmitter['channel'] == transmitter['channel'] + 2 or other_transmitter['channel'] == transmitter['channel'] - 2:
                                    transmitter_power -= other_transmitter["aclr_2"]
                                    PRX_dBm_other_transmitter = transmitter_power - fsl_dB_other_transmitter
                                    PRX_mW = self.dBm_to_mW(PRX_dBm_other_transmitter)
                                    interference = interference + PRX_mW
                                else:
                                    interference = interference
                            else:
                                PRX_dBm_other_transmitter = transmitter_power - fsl_dB_other_transmitter
                                PRX_mW = self.dBm_to_mW(PRX_dBm_other_transmitter)
                                interference = interference + PRX_mW

                distance_new_transmitter = self.calculateDistance(coords_transmitter, transmitter_point)
                if distance_new_transmitter != 0:
                    fsl_dB_new_transmitter = self.free_space_loss(distance_new_transmitter)
                    transmitter_power = self.transmitting_power
                    if self.channel != transmitter['channel']:
                        if self.channel == transmitter['channel'] + 1 or self.channel == transmitter['channel'] - 1:
                            transmitter_power -= self.aclr_1
                            PRX_dBm_new_transmitter = transmitter_power - fsl_dB_new_transmitter
                            PRX_mW = self.dBm_to_mW(PRX_dBm_new_transmitter)
                            interference = interference + PRX_mW
                        if self.channel == transmitter['channel'] + 2 or self.channel == transmitter['channel'] - 2:
                            transmitter_power -= self.aclr_2
                            PRX_dBm_new_transmitter = transmitter_power - fsl_dB_new_transmitter
                            PRX_mW = self.dBm_to_mW(PRX_dBm_new_transmitter)
                            interference = interference + PRX_mW
                        else:
                            interference = interference
                    else:
                        PRX_dBm_new_transmitter = transmitter_power - fsl_dB_new_transmitter
                        PRX_mW = self.dBm_to_mW(PRX_dBm_new_transmitter)
                        interference = interference + PRX_mW
                    distance_transmitter = self.calculateDistance((transmitter['coord_x'], transmitter['coord_y']),
                                                                  transmitter_point)
                    fsl_dB_transmitter = self.free_space_loss(distance_transmitter)

                    sinr = self.SINR(transmitter["power"] - fsl_dB_transmitter, self.noise_transmitter, interference)
                    if sinr < 6:
                        print("SINR = ", sinr, "point: ", transmitter_point, " SINR < 6 dB, request rejected!!!")
                        return False
        return True



    def dBm_to_mW(self, value_dBm):
        return 10 ** (value_dBm / 10)


    def mW_to_dBm(self, value_mW):
        return 10 * log10(value_mW)


    def calculateDistance(self, point_1, point_2):
        # print("type point1", point_1[0], type(point_1[0]))
        # print("type point2", point_2[0], type(point_2[0]))
        dist = sqrt(((point_1[0] - point_2[0]) ** 2) + ((point_1[1] - point_2[1]) ** 2))
        return dist


    def SNR(self, power, thermal_noise_power_dBm):
        return power - thermal_noise_power_dBm


    def SINR(self, power, thermal_noise_power_dBm, interference):
        # print("power: ", power, " thermal noise: ", thermal_noise_power_dBm, " interference: ", interference)
        thermal_noise_power_mW = self.dBm_to_mW(thermal_noise_power_dBm)
        # interference = self.dBm_to_mW(interference)
        interference_plus_noise = thermal_noise_power_mW + interference
        interference_plus_noise = self.mW_to_dBm(interference_plus_noise)
        # print("thermal_noise_power_mW: ",thermal_noise_power_mW, "interference: ", interference)
        # print(" interference_plus_noise: ", interference_plus_noise, " power: ", power)
        return power - interference_plus_noise


    def free_space_loss(self, distance):
        fsl_db = 20 * log10(distance) + 20 * log10(self.carrier_frequency) + 92.45
        return fsl_db


    def thermal_noise_power(self):
        return -174 + 10 * log10(self.bandwidth)

def main(request):
    content_type = request.headers['content-type']
    if content_type == 'application/json':
        request_json = request.get_json(silent=True)
        if request_json:
            print("request json: ", request_json)
            coord_x = request_json['coord_x']
            coord_y = request_json['coord_y']
            channel = request_json['channel']
            power = request_json['power']
            aclr_1 = request_json['aclr_1']
            aclr_2 = request_json['aclr_2']
            list_of_transmitters = request_json['users']
            params = request_json['params']
            for param in params:
                if param['name'] == 'bandwidth':
                    bandwidth = int(param['value'])
                if param['name'] == 'carrier_frequency':
                    carrier_frequency = float(param['value'])
                if param['name'] == 'matrix_length':
                    matrix_length = int(param['value'])
                if param['name'] == 'points_spacing':
                    points_spacing = float(param['value'])

    matrix = list(
        itertools.product(np.arange(0, matrix_length, points_spacing), np.arange(0, matrix_length, points_spacing)))
    point_with_snr = []
    calculation = Calculation((coord_x, coord_y),matrix, list_of_transmitters, power, channel, bandwidth, carrier_frequency, aclr_1, aclr_2)
    accept, points = calculation.is_accept()
    if accept:
        return str(points)
    return 'no_access'

if __name__ == "__main__":
    main(request)