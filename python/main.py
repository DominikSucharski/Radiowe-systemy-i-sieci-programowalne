import geopy.distance
from math import *
import matplotlib.pyplot as plt
import numpy as np
import itertools
import random
from math import *


class Calculation():

    def __init__(self, coords_user, matrix, list_of_trensmitters, transmitting_power, frequency):
        self.frequency = frequency
        self.transmitting_power = transmitting_power
        self.matrix = matrix
        self.accept = False


        if not list_of_trensmitters:
            points, radius = self.calculate_snr_for_points( coords_user)
            list_of_trensmitters.append({"coords": coords_user, "frequency": frequency, "power": transmitting_power, "radius": radius, "point": points})
            self.show_map(list_of_trensmitters)
            self.accept = True
        else:
            accept = self.check_sinr_for_points_other_transmitters( coords_user, list_of_trensmitters)
            if accept:
                points, radius = self.calculate_snr_for_points(coords_user)
                list_of_trensmitters.append(
                    {"coords": coords_user, "frequency": frequency, "power": transmitting_power, "radius": radius,
                     "point": points})
                self.show_map(list_of_trensmitters)
            print("accept: ", accept)

        # distance = geopy.distance.geodesic(coords_user, coords_added_user).km
        # print("distance: ", distance)
    def is_accept(self):
        return self.accept

    def show_map(self, transmitters):
        fig, ax = plt.subplots()
        plt.grid(linestyle='--')
        ax.set(xlim=(0, 10000), ylim=(0, 10000))
        print("len of transmitters: ", len(transmitters), transmitters)
        for i, transmitter in enumerate(transmitters):
            r = random.random()
            g = random.random()
            b = random.random()
            a_circle = plt.Circle(transmitter["coords"], int(transmitter["radius"]), color=(r,g,b))
            ax.add_artist(a_circle)
        plt.show()




    def calculate_snr_for_points(self, coords_transmitter):
        points_snr_greater_than_6dB = []
        distances = []
        print("matrix: ", self.matrix)
        for point in self.matrix:
            distance = self.calculateDistance(point, coords_transmitter)
            if distance == 0:
                pass
            else:
                fsl_dB = self.free_space_loss(distance , self.frequency)
                PRX = self.transmitting_power - fsl_dB
                thermal_noise_power_dBm = self.thermal_noise_power(self.frequency)
                print("thermal noise: ", thermal_noise_power_dBm)
                snr = self.SNR(PRX, thermal_noise_power_dBm)
                print("snr: ", snr)
                if snr > 6:
                    print("snr: ", snr)
                    distances.append(distance)
                    points_snr_greater_than_6dB.append(point)

        # plt.xlim(0, 10000000)
        # plt.ylim(0, 10000000)
        # plt.grid(linestyle='--')
        # plt.title("SINR map")
        # plt.xlabel("latidue")
        # plt.ylabel("longtidue")
        # d = []
        # for i, point in enumerate(points_snr_greater_than_6dB):
        #     d.append(self.calculateDistance(point, coords_transmitter))
        #     plt.scatter(point[0], point[1], s=6)
        # print("max: ", max(d))
        # plt.show()
        return points_snr_greater_than_6dB, max(distances)

    def check_sinr_for_points_other_transmitters(self, coords_transmitter, list_of_trensmitters):
        for transmitter in list_of_trensmitters:
            other_transmitters = list_of_trensmitters.copy().remove(transmitter)
            for transmitter_point in transmitter["point"]:
                interference = 0
                if other_transmitters:
                    for other_transmitter in other_transmitters:
                        distance = self.calculateDistance(transmitter_point, other_transmitter["coords"])
                        if distance == 0:
                            pass
                        else:
                            fsl_dB_transmitter = self.free_space_loss(distance, transmitter["frequency"])
                            PRX_dBm_transmitter = self.transmitting_power - fsl_dB_transmitter
                            PRX_mW = self.dBm_to_mW(PRX_dBm_transmitter)
                            print("PRX: ", PRX_dBm_transmitter)
                            interference = interference + PRX_mW
                noise_transmitter = self.thermal_noise_power(transmitter["frequency"])
                distance_new_transmitter = self.calculateDistance(coords_transmitter, transmitter_point)
                fsl_dB_new_transmitter = self.free_space_loss(distance_new_transmitter, self.frequency)
                PRX_dBm_new_transmitter = self.transmitting_power - fsl_dB_new_transmitter
                PRX_new_mW = self.dBm_to_mW(PRX_dBm_new_transmitter)
                interference = interference + PRX_new_mW
                interference = self.mW_to_dBm(interference)
                print("interference: ", interference)
                sinr = self.SINR(transmitter["power"], noise_transmitter, interference)
                if sinr < 6:
                    print("SINR = ", sinr, "point: ", transmitter_point, " SINR < 6 dB, request rejected!!!")
                    return False
                else:
                    print("sinr = ", sinr, "point: ", transmitter_point," OK")
        return True

    def dBm_to_mW(self, value_dBm):
        return 10 ** (value_dBm / 10)

    def mW_to_dBm(self, value_mW):
        return 10 * log10(value_mW)

    # def calculate_sinr_points(self, coords_transmitter):
    #     points_snr_greater_than_6dB = []
    #     distances = []
    #     for point in self.point_with_interference:
    #         distance = self.calculateDistance(point["point"], coords_transmitter)
    #         if distance == 0:
    #             pass
    #         else:
    #             fsl_dB = self.free_space_loss(distance, self.frequency)
    #             PRX = self.transmitting_power - fsl_dB
    #             thermal_noise_power_dBm = self.thermal_noise_power(self.frequency)
    #             snr = self.SNR(PRX, self.frequency, thermal_noise_power_dBm)
    #             point["snr"] = point["snr"] + snr
    #             sinr = self.SINR(PRX, self.frequency, point["snr"], thermal_noise_power_dBm)
    #             print("snir: ", sinr)
    #             if sinr > 8:
    #                 distances.append(distance)
    #                 points_snr_greater_than_6dB.append(point["point"])
    #     return points_snr_greater_than_6dB, max(distances)

    def calculateDistance(self, point_1, point_2):
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
        print("thermal_noise_power_mW: ",thermal_noise_power_mW, "interference: ", interference)
        print(" interference_plus_noise: ", interference_plus_noise, " power: ", power)
        return power - interference_plus_noise

    def free_space_loss(self, distance, frequency):
        frequency = frequency / 1000000000
        fsl_db = 20 * log10(distance) + 20 * log10(frequency) + 92.45
        return fsl_db

    def thermal_noise_power(self, frequency):
        return -174 + 10*log10(frequency)

def main():
    list_of_transmitters = []
    matrix = list(itertools.product(np.arange(0, 100001, 100), np.arange(0, 100001, 1000)))
    point_with_snr = []
    # for i in matrix:
    #     point_with_snr.append({"point": i, "snr": 0})
    accept = Calculation((3000, 3000),matrix, list_of_transmitters, 20, 10000000).is_accept()
    print("list of transmitters1: ", list_of_transmitters)
    print("accept: ", accept)
    accept = Calculation((6000, 3200),matrix, list_of_transmitters, 20, 10000000).is_accept()
    print("list of transmitters2: ", list_of_transmitters)
    print("accept: ", accept)

if __name__ == "__main__":
    main()
