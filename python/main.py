import geopy.distance
from math import *
# import matplotlib.pyplot as plt
import numpy as np
import itertools
import json
import random
import ast
from math import *


# import matplotlib
# matplotlib.use( 'tkagg' )


class Calculation():

    def __init__(self, coords_user, matrix, list_of_trensmitters, transmitting_power, channel):
        self.channel = channel
        self.transmitting_power = transmitting_power
        self.matrix = matrix
        self.accept = False
        self.points = None
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
            # list_of_trensmitters.append({"coords": coords_user, "channel": channel, "power": transmitting_power, "radius": radius, "points": self.points})
            # self.show_map(list_of_trensmitters)
            self.accept = True
        else:
            self.accept = self.check_sinr_for_points_other_transmitters(coords_user, list_of_trensmitters)
            print("accept 1: ", self.accept)
            if self.accept:
                self.points, radius = self.calculate_snr_for_points(coords_user)

    def is_accept(self):
        print("accept: ", self.accept)
        if self.accept:
            return self.accept, list(self.points)
        else:
            return self.accept, []

    def show_map(self, transmitters):
        fig, ax = plt.subplots()
        plt.grid(linestyle='--')
        ax.set(xlim=(0, 10000), ylim=(0, 10000))
        list_of_colors = [(0, 0, 0.5), (0, 0.5, 0), (0.5, 0, 0), (0.5, 0.5, 0), (0, 0.5, 0.5), (0.5, 0, 0.5),
                          (0.5, 0.5, 0.5)]
        for i, transmitter in enumerate(transmitters):
            # r = random.random()
            # g = random.random()
            # b = random.random()
            a_circle = plt.Circle(transmitter["coords"], int(transmitter["radius"]), color=list_of_colors[i])
            ax.add_artist(a_circle)
        plt.show()

    def channel_to_frequency(self, channel):
        if channel == 1:
            return 10000000
        if channel == 2:
            return 20000000
        if channel == 3:
            return 30000000
        if channel == 4:
            return 40000000
        if channel == 5:
            return 50000000
        if channel == 6:
            return 60000000
        if channel == 7:
            return 70000000
        if channel == 8:
            return 80000000
        if channel == 9:
            return 90000000
        if channel == 10:
            return 100000000

    def calculate_snr_for_points(self, coords_transmitter):
        points_snr_greater_than_6dB = []
        distances = []
        for point in self.matrix:
            distance = self.calculateDistance(point, coords_transmitter)
            if distance != 0:
                fsl_dB = self.free_space_loss(distance, self.channel_to_frequency(self.channel))
                PRX = self.transmitting_power - fsl_dB
                thermal_noise_power_dBm = self.thermal_noise_power(self.channel_to_frequency(self.channel))
                # print("thermal noise: ", thermal_noise_power_dBm)
                snr = self.SNR(PRX, thermal_noise_power_dBm)
                # print("snr: ", snr)
                if snr > 6:
                    print("distance: ", distance)
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
        return points_snr_greater_than_6dB, max(distances)

    def check_sinr_for_points_other_transmitters(self, coords_transmitter, list_of_trensmitters):
        for transmitter in list_of_trensmitters:
            other_transmitters = list_of_trensmitters.copy().remove(transmitter)
            for transmitter_point in transmitter["points"]:
                print("transmitter_point: ", transmitter_point)
                interference = 0
                if other_transmitters:
                    for other_transmitter in other_transmitters:
                        distance = self.calculateDistance(transmitter_point,
                                                          (other_transmitter["coord_x"], other_transmitter["coord_y"]))
                        if distance != 0:
                            fsl_dB_other_transmitter = self.free_space_loss(distance, self.channel_to_frequency(
                                other_transmitter["channel"]))
                            PRX_dBm_other_transmitter = other_transmitter["power"] - fsl_dB_other_transmitter
                            PRX_mW = self.dBm_to_mW(PRX_dBm_other_transmitter)
                            interference = interference + PRX_mW
                noise_transmitter = self.thermal_noise_power(self.channel_to_frequency(transmitter["channel"]))
                distance_new_transmitter = self.calculateDistance(coords_transmitter, transmitter_point)
                fsl_dB_new_transmitter = self.free_space_loss(distance_new_transmitter,
                                                              self.channel_to_frequency(self.channel))
                PRX_dBm_new_transmitter = self.calculate_power_with_aclr(None, list_of_trensmitters, self.channel,
                                                                         self.transmitting_power) - fsl_dB_new_transmitter
                PRX_new_mW = self.dBm_to_mW(PRX_dBm_new_transmitter)
                interference = interference + PRX_new_mW
                interference = self.mW_to_dBm(interference)
                distance_transmitter = self.calculateDistance((transmitter['coord_x'], transmitter['coord_y']),
                                                              transmitter_point)
                fsl_dB_transmitter = self.free_space_loss(distance_transmitter,
                                                          self.channel_to_frequency(transmitter['channel']))
                sinr = self.SINR(transmitter["power"] - fsl_dB_transmitter, noise_transmitter, interference)
                if sinr < 6:
                    # print("SINR = ", sinr, "point: ", transmitter_point, " SINR < 6 dB, request rejected!!!")
                    return False
                # else:
                # print("sinr = ", sinr, "point: ", transmitter_point," OK")
        return True

    def calculate_power_with_aclr(self, transmitter, list_of_transmitters, channel, PTX):
        if transmitter:
            list_other_transmitters = list_of_transmitters.copy().remove(transmitter)
        else:
            list_other_transmitters = list_of_transmitters.copy()
        if not list_other_transmitters:
            return PTX
        for other_transmitter in list_other_transmitters:
            if other_transmitter['channel'] == channel:
                PTX = PTX
            if other_transmitter['channel'] == channel + 1 or other_transmitter['channel'] == channel - 1:
                PTX = PTX - 40
            elif other_transmitter['channel'] == channel + 2 or other_transmitter['channel'] == channel - 2:
                PTX = PTX - 60
            else:
                PTX = 0
        return PTX

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
        interference = self.dBm_to_mW(interference)
        interference_plus_noise = thermal_noise_power_mW + interference
        interference_plus_noise = self.mW_to_dBm(interference_plus_noise)
        # print("thermal_noise_power_mW: ",thermal_noise_power_mW, "interference: ", interference)
        # print(" interference_plus_noise: ", interference_plus_noise, " power: ", power)
        return power - interference_plus_noise

    def free_space_loss(self, distance, frequency):
        frequency = frequency / 1000000000
        fsl_db = 20 * log10(distance) + 20 * log10(frequency) + 92.45
        return fsl_db

    def thermal_noise_power(self, frequency):
        return -174 + 10 * log10(frequency)


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
            list_of_transmitters = request_json['users']

    matrix = list(itertools.product(np.arange(0, 100001, 100), np.arange(0, 100001, 100)))
    point_with_snr = []
    calculation = Calculation((coord_x, coord_y), matrix, list_of_transmitters, power, channel)
    accept, points = calculation.is_accept()
    if accept:
        return str(points)
    return 'no_access'


if __name__ == "__main__":
    main(request)
