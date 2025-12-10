# Liam Willis
# Machine_Learning.py
# This file is what I used to create the classifier for determining discovery method given a Planet's characteristics
# This resource was helpful when making the Random Forest Classifier: https://www.youtube.com/watch?v=_QuGM_FW9e
# This resource was helpful when considering class imbalances: https://www.youtube.com/watch?v=357s5iATs8o

import pandas as pd
import numpy as np
import matplotlib.pyplot as plt
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import classification_report
from sklearn.metrics import confusion_matrix, ConfusionMatrixDisplay
from sklearn.metrics import balanced_accuracy_score
from imblearn.over_sampling import SMOTE

# This generates the graph detailing the frequency of each discovery method
method_names = ['Transit', 'Radial Velocity', 'Microlensing']
frequencies = [4594, 1275, 262]
colors = ['red', 'blue', 'green']

# This generates the bar graph
plt.bar(method_names, frequencies, color=colors)
plt.xlabel('Discovery Method')
plt.ylabel('Frequencies')
plt.title('Number of Exoplanets Discovered by Method')
plt.show()

# This gets the dataset, gets the relevant rows and columns, and drops columns with missing values
df = pd.read_csv('exoplanet_database.csv')
df = df[['sy_dist', 'pl_orbsmax', 'discoverymethod']]
df = df[df['discoverymethod'].isin(['Transit', 'Radial Velocity', 'Microlensing'])]
df = df.dropna()

# Select the feature and target columns
X = df.iloc[:, 0:2]
y = df.iloc[:, 2]

# Feature engineering: applying log + 1 to each feature
X = np.log1p(X)

# Split the training and test data
X_train, X_test, y_train, y_test = train_test_split(X, y, random_state=64, test_size=0.2, stratify=y)

# SMOTE helps handle the extreme class imbalance I have with my data:
# Transit: 74.21% of all Exoplanets in the dataset used this discovery method
# Radial Velocity: 19.10% of all Exoplanets in the dataset used this discovery method
# Microlensing: 3.92% of all Exoplanets in the dataset used this discovery method

# It works by making "synthetic" examples of under-represented classes to prevent the model from just
# guessing the majority class no matter what (Transit)
smote = SMOTE(random_state=64)
X_train_resampled, y_train_resampled = smote.fit_resample(X_train, y_train)

rf = RandomForestClassifier(
    n_estimators=200,
    max_depth=8,
    min_samples_split=5,
    min_samples_leaf=3,
    max_features='sqrt',
    random_state=64
)
rf.fit(X_train_resampled, y_train_resampled)

y_pred = rf.predict(X_test)
y_train_pred = rf.predict(X_train_resampled)

# This is the statistics for the Random Forest Classifier
print(classification_report(y_test, y_pred))
cm = confusion_matrix(y_test, y_pred)
acc = balanced_accuracy_score(y_test, y_pred)
print("Test data accuracy:" + str(acc))
print("Training data accuracy:" + str(balanced_accuracy_score(y_train_resampled, y_train_pred)))
print("\nConfusion Matrix: Microlensing, Radial Velocity, Transit")
print(cm)

# This generates the plot for the Confusion Matrix
display = ConfusionMatrixDisplay(confusion_matrix=cm, display_labels=['Microlensing', 'Radial Velocity', 'Transit'])
display.plot(cmap=plt.cm.Blues)
plt.title("Discovery Method Confusion Matrix")
plt.show()

# This computes the ranges for the plot overlapping the Decision Boundaries with the Training Data
x_min, x_max = X['sy_dist'].min() - 0.1, X['sy_dist'].max() + 0.1
y_min, y_max = X['pl_orbsmax'].min() - 0.1, X['pl_orbsmax'].max() + 0.1

# This creates a grid of points to use on the Random Forest Classifier
xx, yy = np.meshgrid(
    np.linspace(x_min, x_max, 500),
    np.linspace(y_min, y_max, 500)
)

# This assigns each point on the grid to a class using the classifier
mesh_points = pd.DataFrame(np.c_[xx.ravel(), yy.ravel()], columns=['sy_dist', 'pl_orbsmax'])
Z = rf.predict(mesh_points)

# Map class labels to integers for plotting
class_mapping = {'Transit': 0, 'Radial Velocity': 1, 'Microlensing': 2}
Z_num = np.array([class_mapping[label] for label in Z])
Z_num = Z_num.reshape(xx.shape)

# This draws the actual plot itself
plt.figure(figsize=(10, 6))
plt.contourf(xx, yy, Z_num, alpha=0.3, cmap=plt.cm.Set1)

# This assigns a shape and color to each of the points depending on their class
markers = {'Transit': 'o', 'Radial Velocity': 's', 'Microlensing': '^'}
colors = {'Transit': 'red', 'Radial Velocity': 'green', 'Microlensing': 'blue'}

# For each class...
for cls in y.unique():
    plt.scatter(
        # Plot each element of the training data on the plot
        X_train_resampled[y_train_resampled == cls]['sy_dist'],
        X_train_resampled[y_train_resampled == cls]['pl_orbsmax'],
        c=colors[cls], marker=markers[cls], label=cls, edgecolor='k', alpha=0.7
    )

# This labels the plot axes and adds a title for it
plt.xlabel('log(Planet Distance (Parsecs))')
plt.ylabel('log(Orbital Radius (AUs))')
plt.title('Random Forest Decision Boundaries (Training Data Overlapped)')
plt.legend()
plt.show()

# This computes the ranges for the plot overlapping the Decision Boundaries with the Testing Data
x_min, x_max = X['sy_dist'].min() - 0.1, X['sy_dist'].max() + 0.1
y_min, y_max = X['pl_orbsmax'].min() - 0.1, X['pl_orbsmax'].max() + 0.1

# This creates a grid of points to use on the Random Forest Classifier
xx, yy = np.meshgrid(
    np.linspace(x_min, x_max, 500),
    np.linspace(y_min, y_max, 500)
)

# This assigns each point on the grid to a class using the classifier
mesh_points = pd.DataFrame(np.c_[xx.ravel(), yy.ravel()], columns=['sy_dist', 'pl_orbsmax'])
Z = rf.predict(mesh_points)

# Map class labels to integers for plotting
class_mapping = {'Transit': 0, 'Radial Velocity': 1, 'Microlensing': 2}
Z_num = np.array([class_mapping[label] for label in Z])
Z_num = Z_num.reshape(xx.shape)

# This draws the actual plot itself
plt.figure(figsize=(10, 6))
plt.contourf(xx, yy, Z_num, alpha=0.3, cmap=plt.cm.Set1)

# This assigns a shape and color to each of the points depending on their class
markers = {'Transit': 'o', 'Radial Velocity': 's', 'Microlensing': '^'}
colors = {'Transit': 'red', 'Radial Velocity': 'green', 'Microlensing': 'blue'}

# For each class...
for cls in y.unique():
    plt.scatter(
        # Plot each element of the training data on the plot
        X_test[y_test == cls]['sy_dist'],
        X_test[y_test == cls]['pl_orbsmax'],
        c=colors[cls], marker=markers[cls], label=cls, edgecolor='k', alpha=0.7
    )

# This labels the plot axes and adds a title for it
plt.xlabel('log(Planet Distance (Parsecs))')
plt.ylabel('log(Orbital Radius (AUs))')
plt.title('Random Forest Decision Boundaries (Testing Data Overlapped)')
plt.legend()
plt.show()

