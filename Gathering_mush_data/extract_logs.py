#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Wed Aug 26 15:54:17 2020

@author: Sylvain
"""


import numpy as np
import os


###############################################################################
# This code extract individual logs
###############################################################################



#Unfortunatly those vectors needs to be re-done for ES and EN
################################ FR ###########################################
char_list=['Terrence', 'Raluca', 'Gioele', 'Chun', 'Janice', 'Andie', 'Finola','Ian', 'Frieda', 'Stephen', 'Eleesha', 'Roland', 'Jin Su','Derek','Chao', 'Hua', 'Kuan Ti','Paola']
equ_list=["Antenne","Terminal Astro","Lit","Terminal BIOS","Calculateur","Machine à café","Chambre de Combustion","Terminal de Commandement","Centre de Communication","Module Cryo®","Porte","Dynarcade","Réacteur d'urgence","Réservoir de Fuel","Simulateur de Gravité","Cuisine","Réacteur Latéral","Mycoscan","Distilateur de Stupéfiants","Distilleur de Stupéfiant","Coeur de NERON","Réservoir d'Oxygène","Pasiphae","Patrouilleur","Pilgred","Scanner de Planète","Laboratoire de Recherche","Douche","Sofa suédois","Plot Chirurgical","Douche","Poste de Pilotage","Poste de tir","Accès : Icarus","Jukebox"]
item_list=["Super Savon","Fusil Natamy","Thermos de Café","Bloc de Pense-Bête","Drone de Soutien","Plan du Module Babel","Plan de l'Écholocateur","Plan de l'Extincteur","Plan de la Grenade","Livre","Plan","Plan du Lizaro Jungle","Plan de la Sulfateuse","Plan du Vaguoscope","Plan du Lance-Roquette","Plan du Casque de Visée","Plan du Drone de Soutien","Plan Du Sofa Suédois","Plan Du ThermoSenseur","Plan Du Drapeau Blanc","Apprentron : Astrophysicien","Apprentron : Biologiste","Apprentron : Botaniste","Apprentron : Diplomate","Apprentron : Pompier","Apprentron : Cuistot","Apprentron : Informaticien","Apprentron : Logistique","Apprentron : Médecin","Apprentron : Pilote","Apprentron : Expert Radio","Apprentron : Robotiques","Apprentron : Tireur","Apprentron : Psy","Apprentron : Sprinter","Apprentron : Technicien","Vieux T-Shirt","Débris Plastique","Débris Métallique","Tube Epais","Manuel Du Commandant","Document","De La Recherche Sur Le Mush","Pense-Bête","bacta","betapropyl","eufurysant","nuke","rixolam","ponay","pimp","rosebud","soma","épice","twïnoid","xenox","Module Babel","Foreuse","EchoLocateur","Boussole quadrimetric","Corde","ThermoSenseur","Drapeau Blanc","Anémole","Banane","Bottine","Calebotte","Lianube","Filandru","Fragilane","Goustiflon","Citrouïd","Kubinus","Balargine","pénicule","Pénicule","Penicule","Toupimino","Precati","Clé à Molette","Décapsuleur Alien","Trottinette Anti-Grav.","iTrakie®©","Lentille NCC","Vaguoscope","Armure de Plastenite","Gants de protection","Monture Rocheuse","Casque de Visée","Savon","Combinaison","Tablier intachable","Traqueur","Talkie-Walkie","Bandage","Lubrifiant Alien","Médikit","Sérum Retro-Fongique","Suceur de Spore","Caméra","Caméra Installée","Cartouche Invertébré","Carte Liquide de Magellan","Disquette du Gênome Mush","Souche de test Mush","Myco-Alarme","Gelée à Circuits Imprimés","Morceau de carte stellaire","Bâtonnet Aqueux","Schrödinger","Drone De Soutien","Capsule de Fuel","HydroPot","Panier Repas","Capsule d'Oxygène","Capsule Spatiale","Kit de survie","Sofa Suédois","Sofa Suédois","Asperginulk","Bananier","Bifalon","Cucurbitatrouille","Buitalien","Cactuor","Lianiste","Fiboniccus","Peuplimoune","Mycopia","Platacia","Precatus","Poulmino","Tubiliscus","Jeune Asperginulk","Jeune Bananier","Jeune Bifalon","Jeune Cucurbitatrouille","Jeune Buitalien","Jeune Cactuor","Jeune Lianiste","Jeune Fiboniccus","Jeune Peuplimoune","Jeune Mycopia","Jeune Platacia","Jeune Precatus","Jeune Poulmino","Jeune Tubiliscus","Steak alien","Anabolisant","Café","Ration cuisinée","Barre de Lombrics","Déchets Organiques","Riz soufflé proactif","Patate spatiale","Ration standard","Barre Supravitaminée","Télé Holographique alien","Ruban Adhésif","Extincteur","Bidouilleur","MAD Kube","Micro-onde","Supergélateur","Tabulatrice","Blaster","Grenade","Couteau","Lizaro Jungle","Natamy", "Sulfateuse","Lance-Roquette"]
skills_list=["Anonyme","Parfum Antique","Apprentissage","Astrophysicien","Bacterophilie","Biologiste","Botaniste","Conspirateur","Caféinomane","Cuistot","Sang-froid","Confident","Oeil fou","Créatif","Dialoguiste","Contact Déprimant","Concepteur","Détaché","Persévérant","Dévotion","Diplomatie","Portier","Expert","Fertile","Pompier","Frugivore","Cuisine Fongique","Génie","Gelée Verte","Main Verte","Canonnier","Dur-à-Cuire","Hygiéniste","Infecteur","Intimidant","Informaticien","Leader","Lethargie","Logistique","Seul espoir de l'Humanité","Moisification de Masse","Médecin","Métalo","Motivateur","Esprit du Mycéllium","Mycologiste","Dépression de NERON","Seule amie de NERON","Cauchemardesque","Doigt De Fée","Ninja","Infirmier","Observateur","Méticuleuse","Arriviste","Panique","Paranoïaque","Phagocytose","Physicien","Pilote","Politicien","Polymathe","Polyvalent","Pressentiment","Expert radio","Piratage radio","Rebelle","Robotique","Saboteur","Abnégation","Tireur","Psy","Piège Moisi","Fuyant","Robuste","Résistance à l'Eau","Sprinter","Stratéguerre","Survie","Technicien","Optimiste","Bourreau","Traqueur","Traître","Transfert","Piégeur","Retour Arrière","Persécuteur","Lutteur"]
hunter_list=["Hunter", "Trax","Transport","Arack","Astéroïde","D1000"]
injuries_list=['Absence de bras', 'Articulation du bras fort morte','Balle en ballade', 'Bras brulés','Brulure au 3ème degré sur 90%','Brulûre au 3ème degré sur 50%', "Cerveau à l'air libre",'Doigt cassé', 'Doigt manquant', 'Epaule brisée','Epaule froissée', 'Epaule pulvérisée', "Foie hors d'état",'Hemorragie critique', 'Hémorragie', 'Jambe cassée.','Jambes inutilisables', 'Langue cisailée', 'Main brûlée','Main en charpie', 'Nez explosé', 'Oreille incapacités','Oreille interne déréglé.', 'Oreille pulvérisée','Pied cassé.', 'Pied en bouillie.', 'Poumon à trou','Trauma crânien', 'côtes pétée']
disorders_list=['Vertige chronique.','Vertige','Dépression','Migraine chronique','Agoraphobie','Crabisme','Crise Paranoïaque’,’Episodes psychotiques','Phobie des armes','Dépression','Coprolalie','Episodes psychotiques','Phobie des armes','Ailurophobie','Agoraphobie','Spleen','Vertige chronique.','Crabisme','Crise $skill','Migraine chronique','Spleen','Coprolalie','Ailurophobie']
deseases_list=['Migraine','GastroEntérite','Verdoiement','Morsure Noire','Carence en vitamines','Variole','Eruption cutanée','Reflux Gastriques','Intoxication Alimentaire','Nausée légère','Citrouillite','Grippe','Rhume','Vers Solitaire','Acouphènes Extrême','Rage Spatiale','Infection aïgue','Infection fongique','Tempête sinusale','Rubeole','Syphilis','Allergie au chat','Allergie au mush','Oedeme de Quincke']
project_list=['Hydropots supplémentaires','Bouclier plasma',"Détecteurs d'incendie",'Démantèlement','Rafistolage Général','Coursives blindées','Propulseurs antigrav','Acceleration du processeur','Canon blaster','Isolateur Phonique','Réducteur de trainée','Conduite Oxygénées','Lampes a chaleur','Propulseur de décollage','Drone supplémentaire','Visée Heuristique','Distributeur pneumatique','Lavabo opportun','Détecteurs de pistons défectueux','Thalasso','Protocole ACTOPI','Tas de débris','Filet magnetique','Détecteur à ondes de probabilité','Toréfacteur a fission','Reservoir de Teslatron','Arroseurs automatiques','Agrandissement de la cale','Terminaux auxiliaires','Portail de décollage extra-large','Couveuse hydroponique','Pulsateur inversé','Chauffage au sol','Nano Coccinnelles','Rapatriement magnetique','Radar à ondes spatiales',"Détecteur d'anomalie",'Participation de NERON','Cuisine SNC','Jukebox']
list_of_death=['Auto-Extrait','Roquetté','Assassiné','Daedalus détruit','Plaque de métal','Famine','Dépression fatale','Décapité','Blessures...','Saigné','Abandonné','Aventurier perdu','Combat spatial','Brûlé','Mis en quarantaine par NERON','Aventurier pas assez combatif','Circonstances funestes','Assassinés par NERON','Septicémie','Electrocuté','Aventurier malchanceux','Allergie','Aventurier Trop curieux', "Dans l'espace sans pouvoir respirer","Perdu dans l'espace" ]
####################################é###########################################


all_lists=[char_list,item_list,equ_list,skills_list,hunter_list,
           injuries_list,disorders_list,deseases_list,
           project_list,
           list_of_death]
replace_lists=['$char','$item','$equipment','$skill','$hunter',
               '$injurie','$disorder','$desease',
               "$project",
               "$cause_of_death"]


folder='/Users/Sylvain/Desktop/Personnel/logs_Mush2/'



ev_name  =[]
log_text =[]



###############################################################################
# We are making this little counter to know the number of time repairing hurt someone
###############################################################################
## 'EV:REPAIR_HURT' despite its name it is not only repairing but also other such as pick
## 'EV:OBJECT_NOT_REPAIRED'

for i_ship in os.listdir(folder):
    if os.path.isdir(folder + i_ship):

        sub_folder=folder+i_ship+'/'
        for i_char_file in os.listdir(sub_folder):
            if '.txt' in i_char_file:
                file=sub_folder+i_char_file
            
                log_file=open(file, 'r')
                data=log_file.readlines()
                

                for i in range(0, len(data)):
                    log_i=data[i]
                    
                    if 'EV:' in log_i:
                        start=log_i.find('EV:')
                        end  =log_i[start:].find(']') + start
                        
                        ev_name_i=log_i[start:end]
                        
                        #lets get the text of the log
                        start=log_i[end:].find('| ')+end
                        
                        log_text_temp=log_i[start+2:-1]
                    
                        #now let's remoove the names
                        for i_list in [0,1,2,3,4,5,6,7,8,9]:
                            char_list=all_lists[i_list]
                            new_string=replace_lists[i_list]
                            
                            for i_char in char_list:
                                while(i_char+'*' in log_text_temp 
                                  or i_char+' ' in log_text_temp 
                                  or i_char+'.' in log_text_temp
                                  or i_char+')' in log_text_temp
                                  or i_char+'s' in log_text_temp
                                  or i_char+',' in log_text_temp
                                  or i_char+ "\xa0" in log_text_temp
                                  or i_char+',' in log_text_temp):
                                    
                                    if (i_char+'*' in log_text_temp
                                      or i_char+' ' in log_text_temp
                                      or i_char+'.' in log_text_temp
                                      or i_char+')' in log_text_temp
                                      or i_char+'s' in log_text_temp
                                      or i_char+',' in log_text_temp
                                      or i_char+"\xa0" in log_text_temp
                                      or i_char+',' in log_text_temp):
                                        start_replace=log_text_temp.find(i_char)
                                        end_replace  = start_replace + len(i_char)
                                        log_text_temp = log_text_temp[:start_replace]+new_string+log_text_temp[end_replace:]
                                        
                                    
                                
                        ### remove log of torture and chit chat and premonition
                        if ev_name_i=='EV:TORTURE' or ev_name_i=='EV:CHITCHAT':
                            end=(log_text_temp.find('Ses dernières actions sont')+ 
                                             len('Ses dernières actions sont'))
                            log_text_temp=log_text_temp[:end]+"$list_actions."
                            
                        if ev_name_i==('EV:PREMONITION'):
                            end=(log_text_temp.find('Sa dernière action est')+ 
                                             len('Sa dernière action est'))
                            log_text_temp=log_text_temp[:end]+"$last_action."
                            
                            
                        #### Remove planet names
                        if ev_name_i=='EV:ANALYSE_DONE':
                            log_text_temp = "*$char* a effectué une analyse de $planet_name."
                        #### Remove robots names
                        if ev_name_i=='EV:AC_UP_DRONE':
                            log_text_temp = "*$char* s'acharne un peu sur ce pauvre *$drone_name*. Mais c'est pour son bien. *$drone_name* reçoit l'amélioration *drone_upgrade*."
                        
                        if ev_name_i=='EV:PARASITED_PASSIVE_TRIUMPH_EARNED':
                            log_text_temp ='Bienvenue parmi le Mush *$char*. Vous avez été récompensé avec *?? points de Triomphe*.'
                        
                        if ev_name_i=='EV:NERON_CHAT':
                            log_text_temp =''
                        
                        if ev_name_i=='EV:EVENT':
                            log_text_temp =''
                            
                        if ev_name_i=='EV:EXEC_ACTION':
                            log_text_temp =""
                        
                        
                        
                        
                        if ev_name_i=='EV:EV_CHECK_AMMO_PL':
                            log_text_temp ="Il y a bien assez de munitions pour s'en faire encore quelques uns...Votre appareil dispose de quoi faire feu ?? fois."
                        if ev_name_i=='EV:TRIUMPH_EARNED':
                            log_text_temp ="Vous avez gagné *?? Triomphe*."
                        if ev_name_i=='EV:DMG_DEALT':
                            log_text_temp ="Vous perdez ?? :hp:."
                        if ev_name_i=='EV:OBJECT_SLIMED':
                            log_text_temp ="*$char* projette rapidement une infâme gelée sur un *$equipment*. Il sera cassé d'ici ?? cycles..."
                        
                        
                        if ev_name_i=='EV:TRIUMPH_EARNED_STARMAP_FIRST':
                            log_text_temp ="Vous avez gagné *$amount Triomphe*. L'expédition a ramené un morceau de carte stellaire. Peut-être qu'elle pourra nous mener au Nouvel Eden\xa0!"
                        if ev_name_i=='EV:TRIUMPH_EARNED_STARMAP':
                            log_text_temp ='Vous avez gagné *$amount Triomphe* car vous avez ramené un autre morceau de carte stellaire.'
                        if ev_name_i=='EV:REPAIR_PATROL_TRY_SUCCESS':
                            log_text_temp ='La tentative de\xa0*$char* de réparation du *$patroler_name* a réussi. Youhou\xa0! Il est comme neuf\xa0!'
                        
                        if ev_name_i=='EV:PIPELINE_LEVEL':
                            log_text_temp ='Le niveau de la chambre de combustion est maintenant de : $amount'
                        if ev_name_i=='EV:PA_DOWN':
                            log_text_temp ='Vous avez perdu $amount $pa_pm.'
                        if ev_name_i=='EV:MORAL_DOWN':
                            log_text_temp ='Vous avez perdu $amount moral.'
                        if ev_name_i=='EV:HP_UP':
                            log_text_temp ='Vous avez gagné $amount hp.'
                        if ev_name_i=='EV:HEALED_SELF':
                            log_text_temp ='Vous vous êtes soigné. Vous avez récuperé $amount hp.'
                        if ev_name_i=='EV:HEALED_BY_OTHER':
                            log_text_temp ='*$char* a soigné $amount hp à *$char*.'
                        if ev_name_i=='EV:GLOMERON_TRACES':
                            log_text_temp ='Vous entamez une détection des traces Mush... Niveau pré-fongique $amount'
                            
                        if ev_name_i=='EV:EV_PREGNANT':
                            log_text_temp ='Vous avez gagné *$amount Triomphe* car vous attendez un heureux évènement !' 
                        if ev_name_i=='EV:EV_CHECK_LEVEL':
                            log_text_temp ='Vous essuyez la crasse du compteur et lisez le niveau de la chambre : $amount'
                            
                            
                            
                        if 'INFECTED' in ev_name_i:
                            if "Son niveau de contamination est maintenant de" in log_text_temp:
                                start=log_text_temp.find('Son niveau de contamination est maintenant de')+len('Son niveau de contamination est maintenant de *')
                                log_text_temp=log_text_temp[:start]+'$amount'+log_text_temp[start+1:]
                        
                        log_text_i=log_text_temp
                        
                        
                        if not(log_text_i in log_text):
                            log_text.append(str(log_text_i))
                            ev_name.append(str(ev_name_i))
                    


# for i in range(0, np.size(np.unique(ev_name))):
#     indexes= np.where(ev_name==np.unique(ev_name)[i])
#     if np.size(indexes)>1:
#         #Remoove pronouns
#         if 'une ' + new_string in log_text_temp:
#             log_text_temp=
#         if 'la ' + new_string in log_text_temp:



ev_name=np.array(ev_name)  
log_text=np.array(log_text)
                    


###### save a temp file
save_temp_file= '/Users/sylvain/eMush_project/Gathering_mush_data/log_temp.txt'

saving_file = open(save_temp_file, 'w')
for i in range(0, len(ev_name)):
    saving_file.write('"' +ev_name[i] +'" , "'+ log_text[i] + '"\n')
    
saving_file.close()




# # ###### open the temp file
# ev_name  =[]
# log_text =[]
# read_file = open(save_temp_file, 'r')
# data=read_file.readlines()
# for i in range(0, len(data)):
#     log_i=data[i]   
#     start_ev=1
#     end_ev=log_i[1:].find('"')  
#     start_log=log_i[end_ev+1:].find('"')
#     ev_name.append(str(log_i[start_ev:end_ev]))
#     log_text.append(str(log_i[start_log:])) 
# ev_name=np.array(ev_name)  
# log_text=np.array(log_text)















    
    
