import pandas
import zipfile
import datetime as dt
import glob

FEED_DIR = "inventory_feeds/dealerspecialties/"

# determine latest file by today's date
latest_file = glob.glob(FEED_DIR + dt.date.today().strftime('%y%m%d') + '*.zip')[-1]

# open zip file for reading
archive = zipfile.ZipFile(latest_file, 'r')

# load contents of vehicles.txt and links.txt
vehicles = archive.open('VEHICLES.TXT')
links = archive.open('LINKS.TXT')

# read csv of vehicles and links and convert them to DataFrames
df_vehicles = pandas.read_csv(vehicles)
df_links = pandas.read_csv(links)

# merge them together by VIN with the duplicate columns being suffixed with _links
df_merged = df_vehicles.merge(df_links, on='VIN', suffixes=('', '_links'))

# output results to dealerspecialties.csv
df_merged.to_csv(FEED_DIR + 'dealerspecialties.csv', index=False)

# take each DealerID and create its own csv from the main one
for did in df_merged.DealerID.unique():
    df_merged[df_merged['DealerID'] == did].to_csv(FEED_DIR + str(did) + '.csv', index=False)
